<?php
$request_uri = $_SERVER['REQUEST_URI'];
$path = parse_url($request_uri, PHP_URL_PATH);

if (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|html|woff|ttf)$/', $path)) {
    if (file_exists(__DIR__ . $path)) {
        return false;
    }
}

if (strpos($path, '/api/') === 0) {
    require_once __DIR__ . '/api/index.php';
    exit;
}

if (file_exists('index.html')) {
    header('Content-Type: text/html');
    readfile('index.html');
    exit;
}

header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'SIFER API funcionando',
    'endpoints' => ['/api/', '/api/productos', '/api/login']
]);
?>
