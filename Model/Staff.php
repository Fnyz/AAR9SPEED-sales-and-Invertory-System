<?php
require_once __DIR__ . '/../config/Database/Database.php';

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

    /** Staff with role name and how many sales they recorded. */
    public function listWithStats()
    {
        $sql = "SELECT s.STAFF_ID, s.STAFF_USER, s.STAFF_FNAME, s.STAFF_LNAME, s.STAFF_ADDRESS, s.STAFF_EMAIL,
                       s.STAFF_PHONENUM, s.role_id, s.STAFF_CREATED_AT, r.roles,
                       (SELECT COUNT(DISTINCT t.trans_code) FROM transactions t WHERE t.STAFF_ID = s.STAFF_ID) AS sales
                FROM staff s LEFT JOIN role r ON r.role_id = s.role_id
                ORDER BY s.role_id, s.STAFF_USER";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Sales and purchase orders recorded by this staff member (blocks deleting them). */
    public function usageCount(): int
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT (SELECT COUNT(*) FROM transactions WHERE STAFF_ID = :id1) + (SELECT COUNT(*) FROM purchase_order WHERE STAFF_ID = :id2)"
        );
        $stmt->execute([':id1' => $this->id, ':id2' => $this->id]);
        return (int)$stmt->fetchColumn();
    }

    public function usernameExists(string $username): bool
    {
        $stmt = $this->getConnect()->prepare("SELECT COUNT(*) FROM staff WHERE STAFF_USER = :u");
        $stmt->execute([':u' => $username]);
        return (int)$stmt->fetchColumn() > 0;
    }

    public function adminCount(): int
    {
        return (int)$this->getConnect()->query("SELECT COUNT(*) FROM staff WHERE role_id = 1")->fetchColumn();
    }
}
