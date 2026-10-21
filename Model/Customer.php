<?php
require_once __DIR__ . '/../config/Database/Database.php';

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

    /** Customers with purchase count, total spent and last purchase date. */
    public function listWithStats()
    {
        $sql = "SELECT c.*, COUNT(DISTINCT t.trans_code) AS purchases,
                       COALESCE(SUM(t.TRANS_PRICE), 0) AS spent, MAX(t.TRANS_CREATED_AT) AS last_purchase
                FROM customer c LEFT JOIN transactions t ON t.CUS_ID = c.CUS_ID
                GROUP BY c.CUS_ID
                ORDER BY c.CUS_FNAME, c.CUS_LNAME";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Number of sales lines that point at this customer (blocks deleting them). */
    public function usageCount(): int
    {
        $stmt = $this->getConnect()->prepare("SELECT COUNT(*) FROM transactions WHERE CUS_ID = :id");
        $stmt->execute([':id' => $this->id]);
        return (int)$stmt->fetchColumn();
    }
}
