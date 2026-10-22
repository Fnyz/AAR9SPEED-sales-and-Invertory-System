<?php
require_once __DIR__ . '/../config/Database/Database.php';

class Sale extends Database
{
    public const WALK_IN_CUSTOMER = 1;
    public const SERVICE_QTY = 999999;

    /** Active products for the sale screen, with category for filtering. */
    public function sellableItems(): array
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT i.ITEM_ID, i.ITEM_CODE, i.ITEM_NAME, i.ITEM_PRICE, i.ITEM_QUANTITY, i.ITEM_REORDER_LEVEL, i.ITEM_IMAGE,
                    i.CATEGORY_ID, c.CATEGORY_NAME
             FROM item i LEFT JOIN category c ON c.CATEGORY_ID = i.CATEGORY_ID
             WHERE i.ITEM_STATUS = 'ACTIVE' AND i.ITEM_QUANTITY > 0
             ORDER BY i.ITEM_NAME"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function customers(): array
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT CUS_ID, CUS_FNAME, CUS_LNAME FROM customer ORDER BY CUS_ID = " . self::WALK_IN_CUSTOMER . " DESC, CUS_FNAME, CUS_LNAME"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Record a sale. $lines is [[item_id, qty], …]. Prices and stock come from the
     * database, never from the browser. Everything is saved in one transaction.
     * Returns ['code' => invoice code] or ['error' => message for the cashier].
     */
    public function checkout(array $lines, int $customerId, int $staffId, string $method, float $cashReceived, string $reference): array
    {
        $method = strtoupper($method);
        if (!in_array($method, ['CASH', 'GCASH'], true)) {
            return ['error' => 'Choose Cash or GCash.'];
        }
        // Merge duplicate lines and drop empty ones.
        $wanted = [];
        foreach ($lines as $line) {
            $id = (int)($line[0] ?? 0);
            $qty = (int)($line[1] ?? 0);
            if ($id > 0 && $qty > 0) {
                $wanted[$id] = ($wanted[$id] ?? 0) + $qty;
            }
        }
        if (!$wanted) {
            return ['error' => 'Add at least one product to the sale.'];
        }
        if (count($wanted) > 100) {
            return ['error' => 'A sale can have at most 100 different products.'];
        }

        $conn = $this->getConnect();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');
        $conn->exec("SET time_zone = '" . (new DateTime())->format('P') . "'");

        $customer = $conn->prepare("SELECT COUNT(*) FROM customer WHERE CUS_ID = :id");
        $customer->execute([':id' => $customerId]);
        if ((int)$customer->fetchColumn() === 0) {
            $customerId = self::WALK_IN_CUSTOMER;
        }

        $conn->beginTransaction();
        try {
            // Lock the rows so two cashiers can't sell the last unit twice.
            $placeholders = implode(',', array_fill(0, count($wanted), '?'));
            $find = $conn->prepare(
                "SELECT ITEM_ID, ITEM_NAME, ITEM_PRICE, ITEM_COST, ITEM_QUANTITY, ITEM_STATUS FROM item
                 WHERE ITEM_ID IN ($placeholders) FOR UPDATE"
            );
            $find->execute(array_keys($wanted));
            $items = [];
            foreach ($find->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $items[(int)$row['ITEM_ID']] = $row;
            }

            $total = 0.0;
            foreach ($wanted as $id => $qty) {
                $item = $items[$id] ?? null;
                if (!$item || strtoupper($item['ITEM_STATUS']) !== 'ACTIVE') {
                    $conn->rollBack();
                    return ['error' => 'A product in this sale is no longer available. Remove it and try again.'];
                }
                if ($qty > (int)$item['ITEM_QUANTITY']) {
                    $conn->rollBack();
                    return ['error' => 'Only ' . (int)$item['ITEM_QUANTITY'] . ' ' . $item['ITEM_NAME'] . ' left in stock.'];
                }
                $total += round((float)$item['ITEM_PRICE'] * $qty, 2);
            }

            if ($method === 'CASH' && $cashReceived + 0.001 < $total) {
                $conn->rollBack();
                return ['error' => 'Cash received is less than the total of ₱' . number_format($total, 2) . '.'];
            }
            $payment = $method === 'CASH' ? round($cashReceived, 2) : $total;
            $reference = $method === 'GCASH' ? substr(trim($reference), 0, 50) : '';

            $code = $this->newInvoiceCode($conn);
            $insert = $conn->prepare(
                "INSERT INTO transactions (ITEM_ID, CUS_ID, STAFF_ID, trans_code, TRANS_QUANTITY, TRANS_CREATED_AT,
                                           TRANS_UPDATED_AT, TRANS_PRICE, TRANS_UNIT_COST, TRANS_PAYMENT, TRANS_PAYMENT_METHOD, TRANS_PAYMENT_REF)
                 VALUES (:item, :customer, :staff, :code, :qty, now(), now(), :price, :cost, :payment, :method, :ref)"
            );
            $reduce = $conn->prepare(
                "UPDATE item SET ITEM_QUANTITY = ITEM_QUANTITY - :qty, ITEM_UPDATED_AT = now() WHERE ITEM_ID = :id"
            );
            foreach ($wanted as $id => $qty) {
                $insert->execute([
                    ':item' => $id, ':customer' => $customerId, ':staff' => $staffId, ':code' => $code, ':qty' => $qty,
                    ':price' => round((float)$items[$id]['ITEM_PRICE'] * $qty, 2), ':payment' => $payment,
                    // Cost at the time of sale, so profit stays right after costs change.
                    ':cost' => $items[$id]['ITEM_COST'],
                    ':method' => $method, ':ref' => $reference !== '' ? $reference : null,
                ]);
                $reduce->execute([':qty' => $qty, ':id' => $id]);
            }

            // Mark sold-out products out of stock. The database trigger does the same,
            // but shared hosts often don't allow triggers, so don't rely on it.
            $conn->prepare(
                "UPDATE item SET ITEM_STATUS = 'INACTIVE' WHERE ITEM_ID IN ($placeholders) AND ITEM_QUANTITY <= 0"
            )->execute(array_keys($wanted));

            $conn->commit();
            return ['code' => $code, 'total' => $total];
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('checkout failed: ' . $e->getMessage());
            return ['error' => 'The sale could not be saved. Nothing was charged. Please try again.'];
        }
    }

    /** Same style as the existing invoices: last digit of the year + mmddHHMMSS. */
    private function newInvoiceCode(PDO $conn): string
    {
        $exists = $conn->prepare("SELECT COUNT(*) FROM transactions WHERE trans_code = :code");
        $code = substr(date('Y'), -1) . date('mdHis');
        $exists->execute([':code' => $code]);
        $suffix = 2;
        $base = $code;
        while ((int)$exists->fetchColumn() > 0) {
            $code = $base . '-' . $suffix++;
            $exists->execute([':code' => $code]);
        }
        return $code;
    }
}
