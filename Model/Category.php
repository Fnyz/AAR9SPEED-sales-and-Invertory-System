<?php
include_once "config/Database/Database.php";

class Category extends Database
{

    public $cname;
    public $id;
    public $table = "category";

    public function insertCat()
    {
        $sql = "INSERT INTO " . $this->table . " (CATEGORY_NAME) VALUES (:CATEGORY_NAME)";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":CATEGORY_NAME", $this->cname);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function showCategory()
    {
        $sql = "SELECT * FROM " . $this->table;
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteData()
    {
        $sql = "DELETE FROM " . $this->table . " WHERE CATEGORY_ID = :CATEGORY_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":CATEGORY_ID", $this->id);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getSingle()
    {
        $sql = "SELECT * FROM " . $this->table . " WHERE CATEGORY_ID = :CATEGORY_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":CATEGORY_ID", $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function UpdateMe()
    {
        $sql = "UPDATE " . $this->table . " SET CATEGORY_NAME = :CATEGORY_NAME WHERE CATEGORY_ID = :CATEGORY_ID";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":CATEGORY_ID", $this->id);
        $stmt->bindParam(":CATEGORY_NAME", $this->cname);

        if ($stmt->execute()) {
            return true;
        }
        return false;
    }
}
