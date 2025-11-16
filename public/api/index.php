<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ProductosController.php';
require_once __DIR__ . '/controllers/TicketsController.php';
require_once __DIR__ . '/controllers/UsuariosController.php';

header('Content-Type: application/json');

$request_uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

$path = parse_url($request_uri, PHP_URL_PATH);
$api_path = str_replace('/api', '', $path);
$api_path = rtrim($api_path, '/') ?: '/';

try {
    switch (true) {
        case $api_path === '/login' && $method === 'POST':
            $controller = new AuthController();
            $controller->login();
            break;
            
        case $api_path === '/' && $method === 'POST':
            echo json_encode(['success' => true, 'message' => 'ok']);
            break;
            
        default:
            $user = authenticate();
            
            switch (true) {
                case $api_path === '/productos' && $method === 'POST':
                    $controller = new ProductosController();
                    $controller->getAll();
                    break;
                    
                case $api_path === '/usuarios' && $method === 'POST':
                    $controller = new UsuariosController();
                    $controller->getAll();
                    break;
                    
                case $api_path === '/tickets' && $method === 'GET':
                    $controller = new TicketsController();
                    $controller->getAll();
                    break;
                    
                case $api_path === '/tickets' && $method === 'POST':
                    $controller = new TicketsController();
                    $controller->create();
                    break;
                    
                case $api_path === '/tickets/estado' && $method === 'PUT':
                    $controller = new TicketsController();
                    $controller->updateStatus();
                    break;
                    
                case preg_match('#^/tickets/usuario/(\d+)$#', $api_path, $matches) && $method === 'POST':
                    $controller = new TicketsController();
                    $controller->getByUser($matches[1]);
                    break;

                case $api_path === '/tickets/cant' && $method === 'PUT':
                    $controller = new TicketsController();
                    $controller->updateApprovedQuantities();
                    break;
                    
                case preg_match('#^/usuarios/(\d+)$#', $api_path, $matches) && $method === 'POST':
                    $controller = new UsuariosController();
                    $controller->getById($matches[1]);
                    break;
                    
                default:
                    http_response_code(404);
                    echo json_encode(['success' => false, 'error' => 'Endpoint no encontrado']);
                    break;
            }
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
}
?>