<?php
class Database
{
    private $server;
    private $user;
    private $pass;
    private $dbname;
    private $conn;

    private function loadEnv(): void
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
        $this->loadEnv();
        $this->server = $_ENV['DB_HOST'] ?? 'localhost';
        $this->user   = $_ENV['DB_USER'] ?? 'root';
        $this->pass   = $_ENV['DB_PASS'] ?? '';
        $this->dbname = $_ENV['DB_NAME'] ?? 'aar9speed';

        $this->conn = null;

        try {
            $dbServer = "mysql:host=" . $this->server . ";dbname=" . $this->dbname;
            $this->conn = new PDO($dbServer, $this->user, $this->pass);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Connection Unsuccessfull!: " . $e->getMessage();
        }

        return $this->conn;
    }
}
