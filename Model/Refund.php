<?php
require_once __DIR__ . '/../config/Database/Database.php';

class Refund extends Database
{
    public ?string $trans_code  = null;
    public ?int    $item_id     = null;
    public ?int    $quantity    = null;
    public ?string $reason      = null;
    public ?string $refunded_by = null;

    /** Why the last processRefund() call was refused, for showing to the user. */
    public ?string $error = null;

    /** Items on an invoice with how many were sold, already refunded, and can still be refunded. */
    public function invoiceLines(string $transCode): array
    {
        $db   = new Database();
        $stmt = $db->getConnect()->prepare(
            "SELECT t.ITEM_ID, i.ITEM_NAME, i.ITEM_CODE,
                    SUM(t.TRANS_QUANTITY) AS sold,
                    SUM(t.TRANS_PRICE) / NULLIF(SUM(t.TRANS_QUANTITY), 0) AS unit_price,
                    COALESCE((SELECT SUM(r.REFUND_QUANTITY) FROM refunds r
                              WHERE r.TRANS_CODE = t.trans_code AND r.ITEM_ID = t.ITEM_ID), 0) AS refunded
             FROM transactions t JOIN item i ON i.ITEM_ID = t.ITEM_ID
             WHERE t.trans_code = :tc
             GROUP BY t.ITEM_ID, i.ITEM_NAME, i.ITEM_CODE, t.trans_code
             ORDER BY i.ITEM_NAME"
        );
        $stmt->execute([':tc' => $transCode]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as &$row) {
            $row['refundable'] = max(0, (int)$row['sold'] - (int)$row['refunded']);
        }
        return $rows;
    }

    public function processRefund(): bool
    {
        $this->error = null;
        // Only refund items that were on this invoice, and never more than were sold
        // minus what was already refunded: each refund puts the units back into stock.
        $line = null;
        foreach ($this->invoiceLines((string)$this->trans_code) as $row) {
            if ((int)$row['ITEM_ID'] === (int)$this->item_id) {
                $line = $row;
            }
        }
        if ($line === null) {
            $this->error = 'That product is not on invoice ' . $this->trans_code . '.';
            return false;
        }
        if ($this->quantity > $line['refundable']) {
            $this->error = $line['refundable'] === 0
                ? 'Every ' . $line['ITEM_NAME'] . ' on this invoice has already been refunded.'
                : 'Only ' . $line['refundable'] . ' ' . $line['ITEM_NAME'] . ' can still be refunded on this invoice.';
            return false;
        }

        $db   = new Database();
        $conn = $db->getConnect();
        $conn->beginTransaction();
        try {
            $ins = $conn->prepare(
                "INSERT INTO refunds (TRANS_CODE, ITEM_ID, REFUND_QUANTITY, REFUND_REASON, REFUNDED_BY)
                 VALUES (:tc, :iid, :qty, :reason, :by)"
            );
            $ins->execute([
                ':tc'     => $this->trans_code,
                ':iid'    => $this->item_id,
                ':qty'    => $this->quantity,
                ':reason' => $this->reason,
                ':by'     => $this->refunded_by,
            ]);
            $upd = $conn->prepare(
                "UPDATE item SET ITEM_QUANTITY = ITEM_QUANTITY + :qty WHERE ITEM_ID = :id"
            );
            $upd->execute([':qty' => $this->quantity, ':id' => $this->item_id]);
            $conn->commit();
            return true;
        } catch (Exception $e) {
            $conn->rollBack();
            error_log('processRefund failed: ' . $e->getMessage());
            $this->error = 'The refund could not be saved. Please try again.';
            return false;
        }
    }

    public function getAllRefunds(): array
    {
        $db   = new Database();
        $conn = $db->getConnect();
        $stmt = $conn->prepare(
            "SELECT r.*, i.ITEM_NAME
             FROM refunds r
             JOIN item i ON r.ITEM_ID = i.ITEM_ID
             ORDER BY r.REFUND_DATE DESC"
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
