<?php
require_once 'config/database.php';
require_once 'models/Database.php';

header('Content-Type: application/json');

try {
    $db = Database::getInstance()->getConnection();
    
    // Probar consulta simple
    $stmt = $db->query("SELECT version() as postgres_version");
    $version = $stmt->fetch();
    
    // Contar tablas
    $stmt = $db->query("SELECT COUNT(*) as table_count FROM information_schema.tables WHERE table_schema = 'public'");
    $tables = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'message' => '✅ CONEXIÓN A NEON EXITOSA',
        'postgres_version' => $version['postgres_version'],
        'tables_count' => $tables['table_count'],
        'database' => DatabaseConfig::DBNAME
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => '❌ Error: ' . $e->getMessage()
    ]);
}
?>