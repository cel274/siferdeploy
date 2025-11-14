<?php
// CORS headers
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

// Log para debugging
error_log("Request: $path");

// Servir archivos estáticos
if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|html|woff|ttf)$/', $path)) {
    if (file_exists(__DIR__ . $path)) {
        return false;
    }
}

// API routes
if (strpos($path, '/api/') === 0) {
    require_once __DIR__ . '/api/index.php';
    exit;
}

// Frontend
if (file_exists('index.html')) {
    header('Content-Type: text/html');
    readfile('index.html');
    exit;
}

// Default response
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'SIFER API funcionando en Railway',
    'timestamp' => time(),
    'endpoints' => [
        '/api/' => 'API Root',
        '/api/productos' => 'Obtener productos',
        '/api/login' => 'Iniciar sesión'
    ]
]);
?>
