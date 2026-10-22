<?php
require_once __DIR__ . '/../config/Database/Database.php';

class Product extends Database
{

    public $prod;
    public $price;
    public $cost;          // what one unit costs the shop; null = not set
    public $reorderLevel;  // alert at or below this; null = shop-wide default
    public $desc;
    public $quantity;
    public $supplier;
    public $category;
    public $table = "item";
    public $id;
    public $code;
    public $item_id;
    public $staff_id;
    public $item_NAME;
    public $supp_id;
    public $place_id;
    public $updateQuan;
    public $img;
    public $status;
    public $activation = "Active";
    public $subject;
    public $msg;

    public function insertItem()
    {
        $sql = "INSERT INTO " . $this->table . " (ITEM_NAME, ITEM_DESC, ITEM_PRICE, ITEM_COST, ITEM_QUANTITY, ITEM_REORDER_LEVEL, CATEGORY_ID, SUPPLIER_ID, ITEM_CREATED_AT, ITEM_UPDATED_AT, ITEM_CODE, ITEM_IMAGE, ITEM_STATUS)
        VALUES (:ITEM_NAME, :ITEM_DESC, :ITEM_PRICE, :ITEM_COST, :ITEM_QUANTITY, :ITEM_REORDER_LEVEL, :CATEGORY_ID, :SUPPLIER_ID, now(), now(), :ITEM_CODE, :ITEM_IMAGE, :ITEM_STATUS)";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":ITEM_NAME", $this->prod);
        $stmt->bindParam(":ITEM_DESC", $this->desc);
        $stmt->bindParam(":ITEM_PRICE", $this->price);
        $stmt->bindParam(":ITEM_COST", $this->cost);
        $stmt->bindParam(":ITEM_QUANTITY", $this->quantity);
        $stmt->bindParam(":ITEM_REORDER_LEVEL", $this->reorderLevel);
        $stmt->bindParam(":CATEGORY_ID", $this->category);
        $stmt->bindParam(":SUPPLIER_ID", $this->supplier);
        $stmt->bindParam(":ITEM_CODE", $this->code);
        $stmt->bindParam(":ITEM_IMAGE", $this->img);
        $stmt->bindParam(":ITEM_STATUS", $this->status);

        if ($stmt->execute()) {
            return true;
        }
    }

    public function notify()
    {
        $sql = "INSERT INTO notification (subject, message, date_in) VALUES (:subject, :message, now())";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":subject", $this->subject);
        $stmt->bindParam(":message", $this->msg);

        if ($stmt->execute()) {
            return true;
        }
    }

    public function getSupplier()
    {
        $sql = "SELECT * FROM supplier";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getCategory()
    {
        $sql = "SELECT * FROM category";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSingle()
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE ITEM_CODE = :ITEM_CODE";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":ITEM_CODE", $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getSingleProd()
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE ITEM_NAME = :ITEM_NAME AND SUPPLIER_ID = :SUPPLIER_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":ITEM_NAME", $this->item_NAME);
        $stmt->bindParam(":SUPPLIER_ID", $this->supplier);
        $stmt->execute();
        return $stmt->rowCount();
    }

    public function UpdateMe()
    {
        $sql = "UPDATE " . $this->table . " SET ITEM_NAME = :ITEM_NAME, ITEM_PRICE = :ITEM_PRICE, ITEM_COST = :ITEM_COST, ITEM_REORDER_LEVEL = :ITEM_REORDER_LEVEL, ITEM_DESC = :ITEM_DESC, ITEM_UPDATED_AT = now(), ITEM_IMAGE = :ITEM_IMAGE WHERE ITEM_ID = :ITEM_ID";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":ITEM_ID", $this->id);
        $stmt->bindParam(":ITEM_NAME", $this->prod);
        $stmt->bindParam(":ITEM_PRICE", $this->price);
        $stmt->bindParam(":ITEM_COST", $this->cost);
        $stmt->bindParam(":ITEM_REORDER_LEVEL", $this->reorderLevel);
        $stmt->bindParam(":ITEM_DESC", $this->desc);
        $stmt->bindParam(":ITEM_IMAGE", $this->img);

        if ($stmt->execute()) {
            return true;
        }
    }

    public function getPending()
    {
        $sql = "select count(ITEM_STATUS) as total_pending from item where ITEM_STATUS = 'pending' AND ITEM_QUANTITY >= 1";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getInactive()
    {
        $sql = "select count(ITEM_STATUS) as total_pending from item where ITEM_STATUS = 'INACTIVE' AND ITEM_QUANTITY <= 0";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Active products with their category and supplier names, for the Products page. */
    public function showActiveWithNames()
    {
        $sql = "SELECT i.ITEM_ID, i.ITEM_CODE, i.ITEM_NAME, i.ITEM_DESC, i.ITEM_PRICE, i.ITEM_COST, i.ITEM_QUANTITY,
                       i.ITEM_REORDER_LEVEL, i.ITEM_IMAGE, i.CATEGORY_ID, i.SUPPLIER_ID, c.CATEGORY_NAME, s.SUPPLIER_COMPANY
                FROM item i
                LEFT JOIN category c ON c.CATEGORY_ID = i.CATEGORY_ID
                LEFT JOIN supplier s ON s.SUPPLIER_ID = i.SUPPLIER_ID
                WHERE i.ITEM_STATUS = 'ACTIVE'
                ORDER BY i.ITEM_NAME ASC";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Add $qty units to an item and return the new quantity, or null if the item doesn't exist. */
    public function addStock(int $itemId, int $qty): ?int
    {
        $conn = $this->getConnect();
        $stmt = $conn->prepare("UPDATE item SET ITEM_QUANTITY = ITEM_QUANTITY + :qty, ITEM_UPDATED_AT = now() WHERE ITEM_ID = :id");
        $stmt->execute([':qty' => $qty, ':id' => $itemId]);
        if ($stmt->rowCount() === 0) {
            return null;
        }
        $read = $conn->prepare("SELECT ITEM_QUANTITY FROM item WHERE ITEM_ID = :id");
        $read->execute([':id' => $itemId]);
        return (int)$read->fetchColumn();
    }

    /** One product by code with category and supplier names, or null. */
    public function detailsByCode(string $code): ?array
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT i.*, c.CATEGORY_NAME, s.SUPPLIER_COMPANY, s.SUPPLIER_PHONENUM
             FROM item i
             LEFT JOIN category c ON c.CATEGORY_ID = i.CATEGORY_ID
             LEFT JOIN supplier s ON s.SUPPLIER_ID = i.SUPPLIER_ID
             WHERE i.ITEM_CODE = :code
             ORDER BY (i.ITEM_STATUS = 'ACTIVE') DESC
             LIMIT 1"
        );
        $stmt->execute([':code' => $code]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /** Units sold and revenue for an item: all time and last 30 days. */
    public function salesStats(int $itemId): array
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT COALESCE(SUM(TRANS_QUANTITY), 0) AS units, COALESCE(SUM(TRANS_PRICE), 0) AS revenue,
                    COALESCE(SUM(CASE WHEN TRANS_CREATED_AT >= NOW() - INTERVAL 30 DAY THEN TRANS_QUANTITY END), 0) AS units_30,
                    COALESCE(SUM(CASE WHEN TRANS_CREATED_AT >= NOW() - INTERVAL 30 DAY THEN TRANS_PRICE END), 0) AS revenue_30,
                    MAX(TRANS_CREATED_AT) AS last_sold
             FROM transactions WHERE ITEM_ID = :id"
        );
        $stmt->execute([':id' => $itemId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Latest sales lines for an item. */
    public function recentSales(int $itemId, int $limit = 8): array
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT t.trans_code, t.TRANS_QUANTITY, t.TRANS_PRICE, t.TRANS_CREATED_AT,
                    TRIM(CONCAT(COALESCE(c.CUS_FNAME, ''), ' ', COALESCE(c.CUS_LNAME, ''))) AS customer
             FROM transactions t LEFT JOIN customer c ON c.CUS_ID = t.CUS_ID
             WHERE t.ITEM_ID = :id ORDER BY t.TRANS_CREATED_AT DESC LIMIT " . (int)$limit
        );
        $stmt->execute([':id' => $itemId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Purchase orders (deliveries) for an item, newest first. */
    public function purchaseHistory(int $itemId, int $limit = 8): array
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT p.PORD_ID, p.PORD_QUANTITY, p.PORD_UNIT_COST, p.PORD_DATE, s.STAFF_USER
             FROM purchase_order p LEFT JOIN staff s ON s.STAFF_ID = p.STAFF_ID
             WHERE p.ITEM_ID = :id ORDER BY p.PORD_DATE DESC, p.PORD_ID DESC LIMIT " . (int)$limit
        );
        $stmt->execute([':id' => $itemId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Products in one status (pending/INACTIVE) with category and supplier names. */
    public function listByStatus(string $status)
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT i.ITEM_ID, i.ITEM_CODE, i.ITEM_NAME, i.ITEM_DESC, i.ITEM_PRICE, i.ITEM_QUANTITY, i.ITEM_IMAGE,
                    i.ITEM_UPDATED_AT, c.CATEGORY_NAME, s.SUPPLIER_COMPANY
             FROM item i
             LEFT JOIN category c ON c.CATEGORY_ID = i.CATEGORY_ID
             LEFT JOIN supplier s ON s.SUPPLIER_ID = i.SUPPLIER_ID
             WHERE LOWER(i.ITEM_STATUS) = LOWER(:status)
             ORDER BY i.ITEM_NAME"
        );
        $stmt->execute([':status' => $status]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
