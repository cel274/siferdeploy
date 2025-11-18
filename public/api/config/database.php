<?php
class DatabaseConfig {
    const HOST = 'shuttle.proxy.rlwy.net';
    const DBNAME = 'sifer';
    const USERNAME = 'root';
    const PASSWORD = 'TSuCpDkwTMYvSaaNxSMJHXKsifsdvZxv';
    const PORT = '14747';
    const CHARSET = 'utf8mb4';
}
try {
    $dsn = "mysql:host=" . DatabaseConfig::HOST . 
           ";port=" . DatabaseConfig::PORT . 
           ";dbname=" . DatabaseConfig::DBNAME . 
           ";charset=" . DatabaseConfig::CHARSET;
    
    $pdo = new PDO($dsn, DatabaseConfig::USERNAME, DatabaseConfig::PASSWORD);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
    global $pdo;
    
    error_log("Conexión a BD establecida correctamente: " . DatabaseConfig::HOST . ":" . DatabaseConfig::PORT);
    
} catch (PDOException $e) {
    error_log("ERROR de conexión BD: " . $e->getMessage());
    
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json', true, 500);
        echo json_encode([
            'success' => false, 
            'error' => 'Error de conexión a la base de datos'
        ]);
        exit();
    } else {
        die("Error de conexión a la base de datos: " . $e->getMessage());
    }
}
?>
