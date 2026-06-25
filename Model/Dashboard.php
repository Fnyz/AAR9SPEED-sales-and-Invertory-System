<?php
include_once "config/Database/Database.php";

class Dashboard extends Database
{

    public $sub;
    public $msg;
    public $table = "notification";
    public $id;
    public $name;
    public $lastName;
    public $add;
    public $email;
    public $phone;
    public $user;
    public $pass2;

    public function insertNotify()
    {
        $sql = "INSERT INTO " . $this->table . " (subject, message, date_in) VALUES (:subject, :message, now())";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":subject", $this->sub);
        $stmt->bindParam(":message", $this->msg);

        if ($stmt->execute()) {
            return true;
        }
    }

    public function showDeplated()
    {
        $sql = "select * from notification group by subject ORDER BY subject DESC";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getLowQuan()
    {
        $sql = "select count(ITEM_QUANTITY) as low_quan from item where ITEM_QUANTITY < 6";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function pending()
    {
        $sql = "select count(ITEM_STATUS) as pending from item where ITEM_STATUS = 'pending'";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sales()
    {
        $sql = "select sum(t.TRANS_PRICE) as tsales from transactions t";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function transac()
    {
        $sql = "select count(distinct(t.TRANS_CODE)) as tTrans from transactions t";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function best_salers()
    {
        $sql = "select * from (
        select rank() over(partition by t.ITEM_NAME order by best_salers desc) as ranks,
        t.ITEM_NAME, sum(s.TRANS_PRICE) as best_salers
        from item t inner join transactions s on t.ITEM_ID = s.ITEM_ID
        GROUP BY t.ITEM_ID ) as x
        where x.ranks < 4";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStafftoUpdate()
    {
        $sql = "select * from staff where STAFF_ID = :STAFF_ID";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(":STAFF_ID", $this->id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function UpdateMeStaff()
    {
        $passClause = $this->pass2 !== null ? ", STAFF_PASS = :STAFF_PASS" : "";
        $sql = "UPDATE staff SET STAFF_USER = :STAFF_USER" . $passClause . ",
        STAFF_FNAME = :STAFF_FNAME, STAFF_LNAME = :STAFF_LNAME, STAFF_ADDRESS = :STAFF_ADDRESS,
        STAFF_EMAIL = :STAFF_EMAIL, STAFF_PHONENUM = :STAFF_PHONENUM, STAFF_UPDATED_AT = now()
        WHERE STAFF_ID = :STAFF_ID";
        $stmt = $this->getConnect()->prepare($sql);

        $stmt->bindParam(":STAFF_ID", $this->id);
        $stmt->bindParam(":STAFF_FNAME", $this->name);
        if ($this->pass2 !== null) {
            $stmt->bindParam(":STAFF_PASS", $this->pass2);
        }
        $stmt->bindParam(":STAFF_LNAME", $this->lastName);
        $stmt->bindParam(":STAFF_ADDRESS", $this->add);
        $stmt->bindParam(":STAFF_EMAIL", $this->email);
        $stmt->bindParam(":STAFF_PHONENUM", $this->phone);
        $stmt->bindParam(":STAFF_USER", $this->user);

        if ($stmt->execute()) {
            return true;
        }
    }
}
