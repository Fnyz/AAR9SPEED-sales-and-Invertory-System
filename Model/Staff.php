<?php
include_once "config/Database/Database.php";

class Staff extends Database
{

    public $name;
    public $lastName;
    public $phone;
    public $email;
    public $add;
    public $table = "staff";
    public $id;
    public $Username;
    public $password;
    public $role;

    public function inserted()
    {
        $sql = "INSERT INTO " . $this->table . " (STAFF_USER, staff_pass, STAFF_FNAME, STAFF_LNAME, STAFF_ADDRESS, STAFF_EMAIL, STAFF_PHONENUM, role_id, STAFF_CREATED_AT, STAFF_UPDATED_AT)
        VALUES (:STAFF_USER, :staff_pass, :STAFF_FNAME, :STAFF_LNAME, :STAFF_ADDRESS, :STAFF_EMAIL, :STAFF_PHONENUM, :role_id, now(), now())";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":STAFF_USER", $this->Username);
        $stmt->bindParam(":staff_pass", $this->password);
        $stmt->bindParam(":STAFF_FNAME", $this->name);
        $stmt->bindParam(":STAFF_LNAME", $this->lastName);
        $stmt->bindParam(":STAFF_ADDRESS", $this->add);
        $stmt->bindParam(":STAFF_EMAIL", $this->email);
        $stmt->bindParam(":STAFF_PHONENUM", $this->phone);
        $stmt->bindParam(":role_id", $this->role);

        if ($stmt->execute()) {
            return true;
        }
    }

    public function showCustomer()
    {
        $sql = "select * from staff inner join role on staff.role_id = role.role_id";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function role()
    {
        $sql = "SELECT * FROM role";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteData()
    {
        $sql = "DELETE FROM " . $this->table . " WHERE STAFF_ID = :STAFF_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":STAFF_ID", $this->id);
        if ($stmt->execute()) {
            return true;
        }
    }

    public function getSingle()
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE STAFF_ID = :STAFF_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":STAFF_ID", $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function UpdateMe()
    {
        $sql = "UPDATE " . $this->table . " SET STAFF_FNAME = :STAFF_FNAME, STAFF_LNAME = :STAFF_LNAME, STAFF_ADDRESS = :STAFF_ADDRESS, STAFF_EMAIL = :STAFF_EMAIL, STAFF_PHONENUM = :STAFF_PHONENUM, STAFF_UPDATED_AT = now() WHERE STAFF_ID = :STAFF_ID";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":STAFF_ID", $this->id);
        $stmt->bindParam(":STAFF_FNAME", $this->name);
        $stmt->bindParam(":STAFF_LNAME", $this->lastName);
        $stmt->bindParam(":STAFF_ADDRESS", $this->add);
        $stmt->bindParam(":STAFF_EMAIL", $this->email);
        $stmt->bindParam(":STAFF_PHONENUM", $this->phone);

        if ($stmt->execute()) {
            return true;
        }
    }
}
