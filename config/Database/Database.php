<?php
class Database
{
    private $server;
    private $user;
    private $pass;
    private $dbname;
    private $conn;

    /** Read .env into $_ENV. Safe to call more than once. */
    public static function loadEnv(): void
    {
        $path = __DIR__ . '/../../.env';
        if (!file_exists($path)) return;
        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            if (str_starts_with(trim($line), '#')) continue;
            [$key, $val] = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($val);
        }
    }

    public function getConnect()
    {
        self::loadEnv();
        $this->server = $_ENV['DB_HOST'] ?? 'localhost';
        $this->user   = $_ENV['DB_USER'] ?? 'root';
        $this->pass   = $_ENV['DB_PASS'] ?? '';
        $this->dbname = $_ENV['DB_NAME'] ?? 'aar9speed';

        $this->conn = null;

        try {
            $dbServer = "mysql:host=" . $this->server . ";dbname=" . $this->dbname;
            $this->conn = new PDO($dbServer, $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            // Several queries rely on MariaDB's relaxed GROUP BY; MySQL 8 rejects
            // them under ONLY_FULL_GROUP_BY, so turn that mode off per connection.
            $this->conn->exec("SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode, 'ONLY_FULL_GROUP_BY', '')");
        } catch (PDOException $e) {
            // Log the details; never show connection errors (host, user) to visitors.
            error_log('Database connection failed: ' . $e->getMessage());
            echo "The system can't reach the database right now. Please try again in a few minutes.";
        }

        return $this->conn;
    }
}
