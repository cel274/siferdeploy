<?php
$base_path = dirname(__DIR__);

error_log("Base path: " . $base_path);

require_once $base_path . '/cors.php';
require_once $base_path . '/api/config/database.php';

require_once $base_path . '/api/controllers/AuthController.php';
require_once $base_path . '/api/controllers/ProductosController.php';
require_once $base_path . '/api/controllers/TicketsController.php';
require_once $base_path . '/api/controllers/UsuariosController.php';

header('Content-Type: application/json');

$request_uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$path = parse_url($request_uri, PHP_URL_PATH);
$api_path = str_replace('/api', '', $path);
$api_path = rtrim($api_path, '/');

if ($api_path === '') {
    $api_path = '/';
}

error_log("API Request: $method $api_path");

try {
    switch (true) {
        case $api_path === '/' && $method === 'GET':
            echo json_encode([
                'success' => true,
                'message' => 'API SIFER funcionando correctamente'
            ]);
            break;

        case $api_path === '/login' && $method === 'POST':
            $controller = new AuthController();
            $controller->login();
            break;

        case $api_path === '/productos' && $method === 'GET':
            $controller = new ProductosController();
            $controller->getAll();
            break;

        case $api_path === '/usuarios' && $method === 'GET':
            $controller = new UsuariosController();
            $controller->getAll();
            break;

        case $api_path === '/tickets' && $method === 'GET':
            $controller = new TicketsController();
            $controller->getAll();
            break;

        case preg_match('#^/tickets/usuario/(\d+)$#', $api_path, $matches) && $method === 'GET':
            $controller = new TicketsController();
            $controller->getByUser($matches[1]);
            break;

        case preg_match('#^/usuarios/(\d+)$#', $api_path, $matches) && $method === 'GET':
            $controller = new UsuariosController();
            $controller->getById($matches[1]);
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'error' => 'Endpoint no encontrado',
                'requested' => $api_path,
                'method' => $method
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    error_log("API Error: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'error' => 'Error interno del servidor'
    ]);
}
?>