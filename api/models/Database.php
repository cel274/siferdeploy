<?php
require_once __DIR__ . '/../config/database.php';

class Database {
    private $pdo;
    private static $instance = null;

    private function __construct() {
        try {
            $dsn = "pgsql:host=" . DatabaseConfig::HOST . 
                   ";port=" . DatabaseConfig::PORT . 
                   ";dbname=" . DatabaseConfig::DBNAME . 
                   ";sslmode=require";
            
            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false
            ];
            
            $this->pdo = new PDO(
                $dsn, 
                DatabaseConfig::USERNAME, 
                DatabaseConfig::PASSWORD, 
                $options
            );
            
        } catch (PDOException $e) {
            die("Error de conexión a Neon: " . $e->getMessage());
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