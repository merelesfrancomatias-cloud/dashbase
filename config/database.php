<?php
class Database {
    private string $host;
    private string $dbName;
    private string $username;
    private string $password;
    private string $charset;
    public  ?PDO   $conn = null;

    public function __construct() {
        // Lee desde variables de entorno — nunca valores hardcodeados aquí
        $this->host     = $_ENV['DB_HOST']    ?? '127.0.0.1';
        $this->dbName   = $_ENV['DB_NAME']    ?? '';
        $this->username = $_ENV['DB_USER']    ?? '';
        $this->password = $_ENV['DB_PASS']    ?? '';
        $this->charset  = $_ENV['DB_CHARSET'] ?? 'utf8mb4';
    }

    public function getConnection(): PDO {
        if ($this->conn !== null) {
            return $this->conn;
        }

        $port = $_ENV['DB_PORT'] ?? '3306';

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->host,
            $port,
            $this->dbName,
            $this->charset
        );

        $this->conn = new PDO($dsn, $this->username, $this->password, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
            PDO::MYSQL_ATTR_FOUND_ROWS   => true,
        ]);

        return $this->conn;
    }
}
