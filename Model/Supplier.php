<?php
include_once "config/Database/Database.php";

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

    public function showCustomer()
    {
        $sql = "SELECT * FROM " . $this->table;
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

    public function getSingle()
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE SUPPLIER_ID = :SUPPLIER_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":SUPPLIER_ID", $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
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
}
