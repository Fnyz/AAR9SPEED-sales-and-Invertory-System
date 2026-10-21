<?php
require_once __DIR__ . '/../config/Database/Database.php';

class Supplier extends Database
{

    public $name;
    public $lastName;
    public $phone;
    public $email;
    public $add;
    public $table = "supplier";
    public $id;
    public $company;

    public function inserted()
    {
        $sql = "INSERT INTO " . $this->table . " (SUPPLIER_FNAME, SUPPLIER_LNAME, SUPPLIER_ADDRESS, SUPPLIER_PHONENUM, SUPPLIER_COMPANY, SUPPLIER_CREATED_AT, SUPPLIER_UPDATED_AT)
        VALUES (:SUPPLIER_FNAME, :SUPPLIER_LNAME, :SUPPLIER_ADDRESS, :SUPPLIER_PHONENUM, :SUPPLIER_COMPANY, now(), now())";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":SUPPLIER_FNAME", $this->name);
        $stmt->bindParam(":SUPPLIER_LNAME", $this->lastName);
        $stmt->bindParam(":SUPPLIER_ADDRESS", $this->add);
        $stmt->bindParam(":SUPPLIER_PHONENUM", $this->phone);
        $stmt->bindParam(":SUPPLIER_COMPANY", $this->company);

        if ($stmt->execute()) {
            return true;
        }
    }

    public function deleteData()
    {
        $sql = "DELETE FROM " . $this->table . " WHERE SUPPLIER_ID = :SUPPLIER_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":SUPPLIER_ID", $this->id);
        if ($stmt->execute()) {
            return true;
        }
    }

    public function UpdateMe()
    {
        $sql = "UPDATE " . $this->table . " SET SUPPLIER_FNAME = :SUPPLIER_FNAME, SUPPLIER_LNAME = :SUPPLIER_LNAME, SUPPLIER_ADDRESS = :SUPPLIER_ADDRESS, SUPPLIER_COMPANY = :SUPPLIER_COMPANY, SUPPLIER_PHONENUM = :SUPPLIER_PHONENUM, SUPPLIER_UPDATED_AT = now() WHERE SUPPLIER_ID = :SUPPLIER_ID";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":SUPPLIER_ID", $this->id);
        $stmt->bindParam(":SUPPLIER_FNAME", $this->name);
        $stmt->bindParam(":SUPPLIER_LNAME", $this->lastName);
        $stmt->bindParam(":SUPPLIER_ADDRESS", $this->add);
        $stmt->bindParam(":SUPPLIER_COMPANY", $this->company);
        $stmt->bindParam(":SUPPLIER_PHONENUM", $this->phone);

        if ($stmt->execute()) {
            return true;
        }
    }

    /** Suppliers with how many products they supply. */
    public function listWithStats()
    {
        $sql = "SELECT s.*, COUNT(i.ITEM_ID) AS products
                FROM supplier s LEFT JOIN item i ON i.SUPPLIER_ID = s.SUPPLIER_ID
                GROUP BY s.SUPPLIER_ID
                ORDER BY s.SUPPLIER_COMPANY";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Number of products from this supplier (blocks deleting it). */
    public function usageCount(): int
    {
        $stmt = $this->getConnect()->prepare("SELECT COUNT(*) FROM item WHERE SUPPLIER_ID = :id");
        $stmt->execute([':id' => $this->id]);
        return (int)$stmt->fetchColumn();
    }
}
