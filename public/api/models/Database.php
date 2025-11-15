<?php
require_once __DIR__ . '/../config/database.php';

class Database {
    private $pdo;
    private static $instance = null;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DatabaseConfig::HOST . ";dbname=" . DatabaseConfig::DBNAME . ";charset=" . DatabaseConfig::CHARSET;
            $this->pdo = new PDO($dsn, DatabaseConfig::USERNAME, DatabaseConfig::PASSWORD);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }
}
?>