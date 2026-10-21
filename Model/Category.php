<?php
require_once __DIR__ . '/../config/Database/Database.php';

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

    /** Categories with how many products use each, largest first. */
    public function showCategoryWithCounts()
    {
        $sql = "SELECT c.CATEGORY_ID, c.CATEGORY_NAME, COUNT(i.ITEM_ID) AS product_count
                FROM " . $this->table . " c LEFT JOIN item i ON i.CATEGORY_ID = c.CATEGORY_ID
                GROUP BY c.CATEGORY_ID, c.CATEGORY_NAME
                ORDER BY product_count DESC, c.CATEGORY_NAME ASC";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function productCount(): int
    {
        $stmt = $this->getConnect()->prepare("SELECT COUNT(*) FROM item WHERE CATEGORY_ID = :CATEGORY_ID");
        $stmt->execute([':CATEGORY_ID' => $this->id]);
        return (int)$stmt->fetchColumn();
    }

    /** True when another category already uses this name (case-insensitive). */
    public function nameExists(string $name, $exceptId = null): bool
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT COUNT(*) FROM " . $this->table . " WHERE LOWER(CATEGORY_NAME) = LOWER(:name) AND CATEGORY_ID <> :except"
        );
        $stmt->execute([':name' => trim($name), ':except' => (int)$exceptId]);
        return (int)$stmt->fetchColumn() > 0;
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
