<?php
include_once "config/Database/Database.php";

class Customer extends Database
{

    public $name;
    public $lastName;
    public $phone;
    public $email;
    public $add;
    public $table = "customer";
    public $id;

    public function inserted()
    {
        $sql = "INSERT INTO " . $this->table . " (CUS_FNAME, CUS_LNAME, CUS_ADDRESS, CUS_EMAIL, CUS_PHONENUM, CUS_CREATED_AT, CUS_UPDATED_AT)
        VALUES (:CUS_FNAME, :CUS_LNAME, :CUS_ADDRESS, :CUS_EMAIL, :CUS_PHONENUM, now(), now())";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":CUS_FNAME", $this->name);
        $stmt->bindParam(":CUS_LNAME", $this->lastName);
        $stmt->bindParam(":CUS_ADDRESS", $this->add);
        $stmt->bindParam(":CUS_EMAIL", $this->email);
        $stmt->bindParam(":CUS_PHONENUM", $this->phone);

        if ($stmt->execute()) {
            return true;
        }
        return false;
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
        $sql = "DELETE FROM " . $this->table . " WHERE CUS_ID = :CUS_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":CUS_ID", $this->id);
        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getSingle()
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE CUS_ID = :CUS_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":CUS_ID", $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function UpdateMe()
    {
        $sql = "UPDATE " . $this->table . " SET CUS_FNAME = :CUS_FNAME, CUS_LNAME = :CUS_LNAME, CUS_ADDRESS = :CUS_ADDRESS, CUS_EMAIL = :CUS_EMAIL, CUS_PHONENUM = :CUS_PHONENUM, CUS_UPDATED_AT = now() WHERE CUS_ID = :CUS_ID";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":CUS_ID", $this->id);
        $stmt->bindParam(":CUS_FNAME", $this->name);
        $stmt->bindParam(":CUS_LNAME", $this->lastName);
        $stmt->bindParam(":CUS_ADDRESS", $this->add);
        $stmt->bindParam(":CUS_EMAIL", $this->email);
        $stmt->bindParam(":CUS_PHONENUM", $this->phone);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
