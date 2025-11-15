<?php
function getDatabaseConnection() {
    $host = getenv('DB_HOST') ?: 'ep-polished-sky-acsk3ur5-pooler.sa-east-1.aws.neon.tech';
    $dbname = getenv('DB_NAME') ?: 'sifer';
    $username = getenv('DB_USER') ?: 'neondb_owner';
    $password = getenv('DB_PASSWORD') ?: 'npg_2XdbVw8QZiYW';
    $port = getenv('DB_PORT') ?: '5432';
    
    $database_url = getenv('DATABASE_URL');
    
    if ($database_url) {
        $url = parse_url($database_url);
        $host = $url['host'];
        $username = $url['user'];
        $password = $url['pass'];
        $dbname = ltrim($url['path'], '/');
        $port = $url['port'] ?? '5432';
    }
    
    try {
        $dsn = "pgsql:host=$host;port=$port;dbname=$dbname;sslmode=require";
        $pdo = new PDO($dsn, $username, $password, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (\PDOException $e) {
        error_log("Neon DB Connection Error: " . $e->getMessage());
        throw new \PDOException("Database connection failed: " . $e->getMessage(), (int)$e->getCode());
    }
}

$pdo = getDatabaseConnection();
?>
