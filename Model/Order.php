<?php
require_once __DIR__ . '/../config/Database/Database.php';

class Order extends Database
{

    public $Item_id;
    public $ids;
    public $Staff_id;
    public $supp_id;
    public $supp_id3;
    public $spp_id;
    public $quan;
    public $id;
    public $table = "staff";
    public $purchase = 'purchase_order';
    public $stats = 'SUCCESS';
    public $newQuan;
    public $active = "ACTIVE";
    public $pending = "pending";
    public $spp_id2;
    public $ids2;
    public $pord_id;
    public $place_id;
    public $role;
    public $admin;

    public function getSupplier()
    {
        $sql = "SELECT * FROM supplier";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteData()
    {
        $sql = "DELETE from purchase_order where pord_id = :pord_id";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":pord_id", $this->id);
        if ($stmt->execute()) {
            return true;
        }
    }

    /** Purchase orders, newest first, with product, supplier and purchaser. */
    public function listOrders()
    {
        $sql = "SELECT p.PORD_ID, p.PORD_QUANTITY, p.PORD_STATUS, p.PORD_DATE, p.PORD_UNIT_COST, i.ITEM_NAME, i.ITEM_CODE,
                       (p.PORD_UNIT_COST * p.PORD_QUANTITY) AS TOTAL_COST, s.SUPPLIER_COMPANY,
                       TRIM(CONCAT(COALESCE(st.STAFF_FNAME, ''), ' ', COALESCE(st.STAFF_LNAME, ''))) AS purchaser, st.STAFF_USER
                FROM purchase_order p
                JOIN item i ON i.ITEM_ID = p.ITEM_ID
                LEFT JOIN supplier s ON s.SUPPLIER_ID = i.SUPPLIER_ID
                LEFT JOIN staff st ON st.STAFF_ID = p.STAFF_ID
                ORDER BY p.PORD_DATE DESC, p.PORD_ID DESC";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Products that can be ordered, with status and supplier, for the order dialog. */
    public function orderableItems()
    {
        $sql = "SELECT i.ITEM_ID, i.ITEM_NAME, i.ITEM_CODE, i.ITEM_QUANTITY, i.ITEM_PRICE, i.ITEM_COST, i.SUPPLIER_ID,
                       LOWER(i.ITEM_STATUS) AS status, s.SUPPLIER_COMPANY
                FROM item i LEFT JOIN supplier s ON s.SUPPLIER_ID = i.SUPPLIER_ID
                WHERE i.ITEM_QUANTITY < 999999
                ORDER BY s.SUPPLIER_COMPANY, i.ITEM_NAME";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Record a purchase order and update stock in one transaction.
     *  restock    – active product: add $qty to stock
     *  activate   – pending product: mark ACTIVE (stock unchanged; order records current stock)
     *  reactivate – out-of-stock product: set stock to $qty and mark ACTIVE
     * $unitCost is what was paid per unit (null = not recorded); when given it
     * also becomes the product's current cost.
     * Returns null on success or a message explaining why it was refused.
     */
    public function createPurchase(string $mode, int $itemId, int $qty, int $staffId, ?float $unitCost = null): ?string
    {
        $expected = ['restock' => 'active', 'activate' => 'pending', 'reactivate' => 'inactive'];
        if (!isset($expected[$mode])) {
            return 'Choose what kind of order this is.';
        }
        $conn = $this->getConnect();
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->beginTransaction();
        try {
            $find = $conn->prepare("SELECT ITEM_QUANTITY, LOWER(ITEM_STATUS) AS status FROM item WHERE ITEM_ID = :id FOR UPDATE");
            $find->execute([':id' => $itemId]);
            $item = $find->fetch(PDO::FETCH_ASSOC);
            if (!$item) {
                $conn->rollBack();
                return 'That product no longer exists.';
            }
            if ($item['status'] !== $expected[$mode]) {
                $conn->rollBack();
                return 'That product is no longer ' . $expected[$mode] . '. Reload the page and try again.';
            }
            if ($mode !== 'activate' && $qty <= 0) {
                $conn->rollBack();
                return 'Enter a quantity of at least 1.';
            }

            if ($mode === 'restock') {
                $conn->prepare("UPDATE item SET ITEM_QUANTITY = ITEM_QUANTITY + :q, ITEM_UPDATED_AT = now() WHERE ITEM_ID = :id")
                    ->execute([':q' => $qty, ':id' => $itemId]);
            } elseif ($mode === 'activate') {
                $qty = (int)$item['ITEM_QUANTITY'];
                $conn->prepare("UPDATE item SET ITEM_STATUS = 'ACTIVE', ITEM_UPDATED_AT = now() WHERE ITEM_ID = :id")
                    ->execute([':id' => $itemId]);
            } else {
                $conn->prepare("UPDATE item SET ITEM_QUANTITY = :q, ITEM_STATUS = 'ACTIVE', ITEM_UPDATED_AT = now() WHERE ITEM_ID = :id")
                    ->execute([':q' => $qty, ':id' => $itemId]);
            }

            $conn->prepare(
                "INSERT INTO purchase_order (ITEM_ID, STAFF_ID, PORD_QUANTITY, PORD_UNIT_COST, PORD_STATUS, PORD_DATE, PORD_CREATED_AT, PORD_UPDATED_AT)
                 VALUES (:item, :staff, :qty, :cost, 'SUCCESS', now(), now(), now())"
            )->execute([':item' => $itemId, ':staff' => $staffId, ':qty' => $qty, ':cost' => $unitCost]);
            if ($unitCost !== null) {
                $conn->prepare("UPDATE item SET ITEM_COST = :cost WHERE ITEM_ID = :id")
                    ->execute([':cost' => $unitCost, ':id' => $itemId]);
            }

            $conn->commit();
            return null;
        } catch (PDOException $e) {
            $conn->rollBack();
            error_log('createPurchase failed: ' . $e->getMessage());
            return 'The order could not be saved. Please try again.';
        }
    }

    /** One purchase order for printing, or null. */
    public function purchaseDocument(int $id): ?array
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT p.PORD_ID, p.PORD_QUANTITY, p.PORD_STATUS, p.PORD_DATE, p.PORD_UNIT_COST,
                    i.ITEM_NAME, i.ITEM_CODE, i.ITEM_DESC, (p.PORD_UNIT_COST * p.PORD_QUANTITY) AS TOTAL_COST,
                    s.SUPPLIER_COMPANY, s.SUPPLIER_ADDRESS, s.SUPPLIER_PHONENUM,
                    TRIM(CONCAT(COALESCE(st.STAFF_FNAME, ''), ' ', COALESCE(st.STAFF_LNAME, ''))) AS purchaser, st.STAFF_USER
             FROM purchase_order p
             JOIN item i ON i.ITEM_ID = p.ITEM_ID
             LEFT JOIN supplier s ON s.SUPPLIER_ID = i.SUPPLIER_ID
             LEFT JOIN staff st ON st.STAFF_ID = p.STAFF_ID
             WHERE p.PORD_ID = :id"
        );
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
}
