<?php
require_once __DIR__ . '/../config/Database/Database.php';

class Invoice extends Database
{

    public $trans_code;

    /** One row per invoice, newest first, with customer, staff, totals and refunded units. */
    public function listInvoices()
    {
        $sql = "SELECT t.trans_code,
                       MIN(t.TRANS_CREATED_AT) AS created_at,
                       SUM(t.TRANS_QUANTITY) AS items,
                       SUM(t.TRANS_PRICE) AS total,
                       MIN(TRIM(CONCAT(COALESCE(c.CUS_FNAME, ''), ' ', COALESCE(c.CUS_LNAME, '')))) AS customer,
                       MIN(TRIM(CONCAT(COALESCE(s.STAFF_FNAME, ''), ' ', COALESCE(s.STAFF_LNAME, '')))) AS staff,
                       MIN(s.STAFF_USER) AS staff_user,
                       COALESCE(MAX(r.refunded), 0) AS refunded
                FROM transactions t
                LEFT JOIN customer c ON c.CUS_ID = t.CUS_ID
                LEFT JOIN staff s ON s.STAFF_ID = t.STAFF_ID
                LEFT JOIN (SELECT TRANS_CODE, SUM(REFUND_QUANTITY) AS refunded FROM refunds GROUP BY TRANS_CODE) r
                       ON r.TRANS_CODE = t.trans_code
                GROUP BY t.trans_code
                ORDER BY created_at DESC";
        $stmt = $this->getConnect()->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Everything a printed receipt needs, or null if the invoice doesn't exist. */
    public function receipt(string $code): ?array
    {
        $conn = $this->getConnect();
        $head = $conn->prepare(
            "SELECT t.trans_code, MIN(t.TRANS_CREATED_AT) AS created_at, MAX(t.TRANS_PAYMENT) AS payment,
                    SUM(t.TRANS_PRICE) AS total, SUM(t.TRANS_QUANTITY) AS items,
                    MIN(TRIM(CONCAT(COALESCE(c.CUS_FNAME, ''), ' ', COALESCE(c.CUS_LNAME, '')))) AS customer,
                    MIN(TRIM(CONCAT(COALESCE(s.STAFF_FNAME, ''), ' ', COALESCE(s.STAFF_LNAME, '')))) AS staff,
                    MIN(s.STAFF_USER) AS staff_user
             FROM transactions t
             LEFT JOIN customer c ON c.CUS_ID = t.CUS_ID
             LEFT JOIN staff s ON s.STAFF_ID = t.STAFF_ID
             WHERE t.trans_code = :code
             GROUP BY t.trans_code"
        );
        $head->execute([':code' => $code]);
        $header = $head->fetch(PDO::FETCH_ASSOC);
        if (!$header) {
            return null;
        }
        $lines = $conn->prepare(
            "SELECT i.ITEM_NAME, i.ITEM_CODE, t.TRANS_QUANTITY AS qty, t.TRANS_PRICE AS line_total,
                    t.TRANS_PRICE / NULLIF(t.TRANS_QUANTITY, 0) AS unit_price
             FROM transactions t JOIN item i ON i.ITEM_ID = t.ITEM_ID
             WHERE t.trans_code = :code ORDER BY t.TRANS_ID"
        );
        $lines->execute([':code' => $code]);
        $refunds = [];
        try {
            $r = $conn->prepare(
                "SELECT r.REFUND_QUANTITY, r.REFUND_DATE, r.REFUND_REASON, i.ITEM_NAME
                 FROM refunds r JOIN item i ON i.ITEM_ID = r.ITEM_ID WHERE r.TRANS_CODE = :code ORDER BY r.REFUND_DATE"
            );
            $r->execute([':code' => $code]);
            $refunds = $r->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // refunds table only exists after migrations/002_improvements.sql
        }
        $header['method'] = 'CASH';
        $header['reference'] = null;
        try {
            $pay = $conn->prepare(
                "SELECT MAX(TRANS_PAYMENT_METHOD) AS method, MAX(TRANS_PAYMENT_REF) AS reference FROM transactions WHERE trans_code = :code"
            );
            $pay->execute([':code' => $code]);
            $header = array_merge($header, array_filter($pay->fetch(PDO::FETCH_ASSOC) ?: [], fn($v) => $v !== null));
        } catch (PDOException $e) {
            // payment method columns only exist after migrations/006_payment_method.sql
        }
        return ['header' => $header, 'lines' => $lines->fetchAll(PDO::FETCH_ASSOC), 'refunds' => $refunds];
    }
}
