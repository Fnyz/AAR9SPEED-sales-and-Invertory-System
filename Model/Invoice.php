<?php
include_once "config/Database/Database.php";

class Invoice extends Database
{

    public $trans_code;

    public function invoiceHistory()
    {
        $sql = "select t.TRANS_CODE, c.CUS_FNAME, sum(t.TRANS_QUANTITY) as total_quan,
        sum(t.TRANS_PRICE) as total_price, s.STAFF_FNAME, t.TRANS_CREATED_AT
        from transactions t inner join customer c on c.CUS_ID = t.CUS_ID
        join staff s on s.STAFF_ID = t.STAFF_ID
        GROUP BY TRANS_CODE";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function transacs()
    {
        $sql = "Select t.TRANS_CODE, c.CUS_FNAME, c.CUS_LNAME, a.ITEM_NAME, t.TRANS_QUANTITY,
        a.ITEM_PRICE as Unit_price, t.TRANS_PRICE, t.TRANS_CREATED_AT, t.TRANS_PAYMENT
        from transactions t inner join item a on t.ITEM_ID = a.ITEM_ID
        join customer c on t.CUS_ID = c.CUS_ID
        where TRANS_CODE = :TRANS_CODE";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(':TRANS_CODE', $this->trans_code);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function totalTrans()
    {
        $sql = "Select sum(t.TRANS_PRICE) as total_price from transactions t where TRANS_CODE = :TRANS_CODE";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(':TRANS_CODE', $this->trans_code);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function totalchange()
    {
        $sql = "select (TRANS_PAYMENT - sum(TRANS_PRICE)) as Total_CHANGE from transactions WHERE TRANS_CODE = :TRANS_CODE";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->bindParam(':TRANS_CODE', $this->trans_code);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
