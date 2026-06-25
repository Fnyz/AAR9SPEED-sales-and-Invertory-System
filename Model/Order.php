<?php
include_once "config/Database/Database.php";

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

    public function inserted()
    {
        $sql = "INSERT INTO " . $this->purchase . " (ITEM_ID, STAFF_ID, PORD_QUANTITY, PORD_STATUS, PORD_DATE, PORD_CREATED_AT, PORD_UPDATED_AT)
        VALUES (:ITEM_ID, :STAFF_ID, :PORD_QUANTITY, :PORD_STATUS, now(), now(), now())";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":ITEM_ID", $this->Item_id);
        $stmt->bindParam(":STAFF_ID", $this->Staff_id);
        $stmt->bindParam(":PORD_QUANTITY", $this->quan);
        $stmt->bindParam(":PORD_STATUS", $this->stats);

        if ($stmt->execute()) {
            return true;
        }
    }

    public function inserted2()
    {
        $sql = "INSERT INTO " . $this->purchase . " (ITEM_ID, STAFF_ID, PORD_QUANTITY, PORD_STATUS, PORD_DATE, PORD_CREATED_AT, PORD_UPDATED_AT)
        VALUES (:ITEM_ID, :STAFF_ID, :PORD_QUANTITY, :PORD_STATUS, now(), now(), now())";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":ITEM_ID", $this->Item_id);
        $stmt->bindParam(":STAFF_ID", $this->Staff_id);
        $stmt->bindParam(":PORD_QUANTITY", $this->quan);
        $stmt->bindParam(":PORD_STATUS", $this->stats);

        if ($stmt->execute()) {
            return true;
        }
    }

    public function showProduct()
    {
        $sql = "select p.PORD_ID as PORD_ID, t.ITEM_NAME, p.PORD_QUANTITY, t.ITEM_PRICE, t.ITEM_STATUS,
        (t.ITEM_PRICE * p.PORD_QUANTITY) as TOTAL_PRICE, s.SUPPLIER_COMPANY, r.STAFF_FNAME, p.PORD_STATUS
        from item t inner join supplier s on t.SUPPLIER_ID = s.SUPPLIER_ID
        join {$this->purchase} p on t.ITEM_ID = p.ITEM_ID
        join staff r on p.STAFF_ID = r.STAFF_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function showReceipt()
    {
        $sql = "select t.ITEM_NAME, P.PORD_QUANTITY, t.ITEM_PRICE,
        (t.ITEM_PRICE * p.PORD_QUANTITY) as TOTAL_PRICE, s.SUPPLIER_COMPANY, r.STAFF_FNAME,
        p.PORD_STATUS, p.PORD_CREATED_AT, t.ITEM_DESC
        from item t inner join supplier s on t.SUPPLIER_ID = s.SUPPLIER_ID
        join purchase_order p on t.ITEM_ID = p.ITEM_ID
        join staff r on p.STAFF_ID = r.STAFF_ID
        WHERE PORD_ID = :PORD_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(':PORD_ID', $this->pord_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSingle()
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE CUS_ID = :CUS_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":CUS_ID", $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getStaff()
    {
        $sql = "select * from staff where role_id = :role_id and STAFF_USER = :STAFF_USER";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":role_id", $this->role);
        $stmt->bindParam(":STAFF_USER", $this->admin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getProds()
    {
        $sql = "SELECT *, sum(ITEM_QUANTITY) as total_quan from item where CATEGORY_ID = :CATEGORY_ID GROUP BY ITEM_CODE";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":CATEGORY_ID", $this->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function placeProds()
    {
        $sql = "SELECT *, sum(ITEM_QUANTITY) as total_quan from item where ITEM_CODE = :ITEM_CODE";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":ITEM_CODE", $this->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSupplier()
    {
        $sql = "SELECT * FROM supplier";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getITEM()
    {
        $sql = "SELECT * FROM item where SUPPLIER_ID = :SUPPLIER_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(':SUPPLIER_ID', $this->supp_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function placeInput()
    {
        $sql = "SELECT * FROM item where ITEM_ID = :ITEM_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(':ITEM_ID', $this->place_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function UpdateMe()
    {
        $sql = "UPDATE item set ITEM_QUANTITY = (ITEM_QUANTITY + :NEW_QUAN) WHERE ITEM_ID = :ITEM_ID AND SUPPLIER_ID = :SUPPLIER_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(':NEW_QUAN', $this->newQuan, PDO::PARAM_INT);
        $stmt->bindParam(':ITEM_ID', $this->ids, PDO::PARAM_INT);
        $stmt->bindParam(':SUPPLIER_ID', $this->spp_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return true;
        }
    }

    public function UpdateMe3()
    {
        $sql = "UPDATE item set ITEM_QUANTITY = :ITEM_QUANTITY, ITEM_STATUS = :ITEM_STATUS WHERE ITEM_ID = :ITEM_ID AND SUPPLIER_ID = :SUPPLIER_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":ITEM_QUANTITY", $this->quan);
        $stmt->bindParam(":ITEM_STATUS", $this->active);
        $stmt->bindParam(":ITEM_ID", $this->ids);
        $stmt->bindParam(":SUPPLIER_ID", $this->spp_id);
        if ($stmt->execute()) {
            return true;
        }
    }

    public function UpdateMe2()
    {
        $sql = "UPDATE item set ITEM_STATUS = 'ACTIVE' WHERE ITEM_ID = :ITEM_ID AND SUPPLIER_ID = :SUPPLIER_ID AND ITEM_STATUS = 'pending'";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(':ITEM_ID', $this->ids2, PDO::PARAM_INT);
        $stmt->bindParam(':SUPPLIER_ID', $this->spp_id2, PDO::PARAM_INT);
        if ($stmt->execute()) {
            return true;
        }
    }

    public function getPendingOnly()
    {
        $sql = "SELECT * FROM item where SUPPLIER_ID = :SUPPLIER_ID AND ITEM_STATUS = 'pending' AND ITEM_QUANTITY >= 1";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(':SUPPLIER_ID', $this->supp_id, PDO::PARAM_INT);
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

    public function getMePAPA()
    {
        $sql = "select * from purchase_order";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getInactiveOnly()
    {
        $sql = "SELECT * FROM item where SUPPLIER_ID = :SUPPLIER_ID AND ITEM_STATUS = 'inactive' AND ITEM_QUANTITY <= 0";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(':SUPPLIER_ID', $this->supp_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
