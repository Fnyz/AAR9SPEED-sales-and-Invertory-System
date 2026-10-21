<?php
require_once __DIR__ . '/../config/Database/Database.php';

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

    public function showDeplated()
    {
        $sql = "select * from notification group by subject ORDER BY subject DESC";
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

    /* ---------- Tabler dashboard ---------- */

    /** Connection on the shop's clock, so "today" matches in PHP and MySQL. */
    private function shopConnect(): PDO
    {
        $conn = $this->getConnect();
        date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'Asia/Manila');
        $conn->exec("SET time_zone = '" . (new DateTime())->format('P') . "'");
        return $conn;
    }

    /** Sales total, transaction count and items sold between two dates (end exclusive). */
    public function salesBetween(string $from, string $to): array
    {
        $stmt = $this->shopConnect()->prepare(
            "SELECT COALESCE(SUM(TRANS_PRICE), 0) AS total,
                    COUNT(DISTINCT trans_code) AS transactions,
                    COALESCE(SUM(TRANS_QUANTITY), 0) AS items
             FROM transactions WHERE TRANS_CREATED_AT >= :from AND TRANS_CREATED_AT < :to"
        );
        $stmt->execute([':from' => $from, ':to' => $to]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** One row per day for the last $days days (today included), zero-filled. */
    public function dailySales(int $days): array
    {
        $conn = $this->shopConnect();
        $start = date('Y-m-d', strtotime('-' . ($days - 1) . ' days'));
        $stmt = $conn->prepare(
            "SELECT DATE(TRANS_CREATED_AT) AS day, SUM(TRANS_PRICE) AS total
             FROM transactions WHERE TRANS_CREATED_AT >= :start
             GROUP BY DATE(TRANS_CREATED_AT)"
        );
        $stmt->execute([':start' => $start]);
        $totals = array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'total', 'day');

        $rows = [];
        for ($i = 0; $i < $days; $i++) {
            $day = date('Y-m-d', strtotime("$start +$i days"));
            $rows[] = ['day' => $day, 'total' => (float)($totals[$day] ?? 0)];
        }
        return $rows;
    }

    /** Items at or below the threshold, lowest first. Services (huge quantities) never qualify. */
    public function lowStockItems(int $threshold, int $limit = 6): array
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT i.ITEM_NAME, i.ITEM_CODE, i.ITEM_QUANTITY, s.SUPPLIER_COMPANY
             FROM item i LEFT JOIN supplier s ON s.SUPPLIER_ID = i.SUPPLIER_ID
             WHERE i.ITEM_QUANTITY <= :threshold
             ORDER BY i.ITEM_QUANTITY ASC, i.ITEM_NAME ASC
             LIMIT " . (int)$limit
        );
        $stmt->execute([':threshold' => $threshold]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function lowStockCounts(int $threshold): array
    {
        $stmt = $this->getConnect()->prepare(
            "SELECT COUNT(*) AS low, SUM(ITEM_QUANTITY <= 1) AS critical, (SELECT COUNT(*) FROM item) AS products
             FROM item WHERE ITEM_QUANTITY <= :threshold"
        );
        $stmt->execute([':threshold' => $threshold]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /** Latest sales, one row per invoice. */
    public function recentTransactions(int $limit = 6): array
    {
        $stmt = $this->shopConnect()->prepare(
            "SELECT t.trans_code,
                    MIN(TRIM(CONCAT(COALESCE(c.CUS_FNAME, ''), ' ', COALESCE(c.CUS_LNAME, '')))) AS customer,
                    SUM(t.TRANS_QUANTITY) AS items,
                    SUM(t.TRANS_PRICE) AS total,
                    MAX(t.TRANS_CREATED_AT) AS created_at
             FROM transactions t LEFT JOIN customer c ON c.CUS_ID = t.CUS_ID
             GROUP BY t.trans_code
             ORDER BY created_at DESC
             LIMIT " . (int)$limit
        );
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Top sellers by revenue since a date. */
    public function bestSellersSince(string $from, int $limit = 5): array
    {
        $stmt = $this->shopConnect()->prepare(
            "SELECT i.ITEM_NAME, SUM(t.TRANS_QUANTITY) AS qty, SUM(t.TRANS_PRICE) AS total
             FROM transactions t JOIN item i ON i.ITEM_ID = t.ITEM_ID
             WHERE t.TRANS_CREATED_AT >= :from
             GROUP BY i.ITEM_ID, i.ITEM_NAME
             ORDER BY total DESC
             LIMIT " . (int)$limit
        );
        $stmt->execute([':from' => $from]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** Telegram automation status; the tables only exist after migrations/003_automation.sql. */
    public function automationStatus(): array
    {
        $this->getConnect(); // loads .env
        $status = [
            'telegram' => ($_ENV['TELEGRAM_BOT_TOKEN'] ?? '') !== '' && ($_ENV['TELEGRAM_CHAT_ID'] ?? '') !== '',
            'report_time' => $_ENV['DAILY_REPORT_TIME'] ?? '21:00',
            'last_alert' => null,
        ];
        try {
            $status['last_alert'] = $this->getConnect()->query("SELECT MAX(alerted_at) FROM stock_alerts")->fetchColumn() ?: null;
        } catch (PDOException $e) {
            // migration not run yet
        }
        return $status;
    }
}
