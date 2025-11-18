<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/config/database.php';

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/ProductosController.php';
require_once __DIR__ . '/controllers/TicketsController.php';
require_once __DIR__ . '/controllers/UsuariosController.php';

header('Content-Type: application/json');

$rawBody = file_get_contents('php://input');
error_log("REQUEST BODY: " . $rawBody);

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

        case $api_path === '/register' && $method === 'POST':
            $controller = new AuthController();
            $controller->register();
            break;
            
        case $api_path === '/' && $method === 'POST':
            echo json_encode(['success' => true, 'message' => 'ok']);
            break;
            
        default:
            $user = ['id' => 1, 'rol' => 1, 'nombre' => 'admin'];
            
            switch (true) {
                case $api_path === '/productos' && $method === 'POST':
                    if ($user['rol'] != 1) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'error' => 'Se requiere rol de administrador']);
                        break;
                    }
                    $controller = new ProductosController();
                    $controller->getAll();
                    break;
                    
                case $api_path === '/usuarios' && $method === 'POST':
                    if ($user['rol'] != 1) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'error' => 'Se requiere rol de administrador']);
                        break;
                    }
                    $controller = new UsuariosController();
                    $controller->getAll();
                    break;
                    
                case $api_path === '/tickets' && $method === 'GET':
                    if ($user['rol'] != 1) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'error' => 'Se requiere rol de administrador']);
                        break;
                    }
                    $controller = new TicketsController();
                    $controller->getAll();
                    break;
                    
                case $api_path === '/tickets' && $method === 'POST':
                    $controller = new TicketsController();
                    $controller->create();
                    break;
                    
                case $api_path === '/tickets/estado' && $method === 'PUT':
                    if ($user['rol'] != 1) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'error' => 'Se requiere rol de administrador']);
                        break;
                    }
                    $controller = new TicketsController();
                    $controller->updateStatus();
                    break;
                    
                case preg_match('#^/tickets/usuario/(\d+)$#', $api_path, $matches) && $method === 'POST':
                    $requestedUserId = (int)$matches[1];
                    if ($user['id'] != $requestedUserId && $user['rol'] != 1) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'error' => 'Solo podés ver tus propios tickets']);
                        break;
                    }
                    $controller = new TicketsController();
                    $controller->getByUser($requestedUserId);
                    break;

                case $api_path === '/tickets/cant' && $method === 'PUT':
                    if ($user['rol'] != 1) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'error' => 'Se requiere rol de administrador']);
                        break;
                    }
                    $controller = new TicketsController();
                    $controller->updateApprovedQuantities();
                    break;
                    
                case preg_match('#^/usuarios/(\d+)$#', $api_path, $matches) && $method === 'POST':
                    if ($user['rol'] != 1) {
                        http_response_code(403);
                        echo json_encode(['success' => false, 'error' => 'Se requiere rol de administrador']);
                        break;
                    }
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
