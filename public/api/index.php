<?php
require_once __DIR__ . '/../cors.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/middleware/auth.php';

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
    $user = authenticate();
    
    switch (true) {
        // ... otros casos se mantienen igual ...
                    
        case $api_path === '/tickets' && $method === 'GET':
            // SOLO ADMIN puede ver todos los tickets
            if ($user['rol'] != 1) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Se requiere rol de administrador']);
                break;
            }
            $controller = new TicketsController();
            $controller->getAll();
            break;
            
        case $api_path === '/tickets' && $method === 'POST':
            // ✅ CUALQUIER usuario autenticado puede crear tickets (sin verificación de rol)
            $controller = new TicketsController();
            $controller->create();
            break;
            
        case $api_path === '/tickets/estado' && $method === 'PUT':
            // SOLO ADMIN puede cambiar estado de tickets
            if ($user['rol'] != 1) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Se requiere rol de administrador']);
                break;
            }
            $controller = new TicketsController();
            $controller->updateStatus();
            break;
            
        case preg_match('#^/tickets/usuario/(\d+)$#', $api_path, $matches) && $method === 'POST':
            // ✅ Usuario puede ver SOLO SUS PROPIOS tickets
            $requestedUserId = (int)$matches[1];
            if ($user['id'] != $requestedUserId && $user['rol'] != 1) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Solo puedes ver tus propios tickets']);
                break;
            }
            $controller = new TicketsController();
            $controller->getByUser($requestedUserId);
            break;

        case $api_path === '/tickets/cant' && $method === 'PUT':
            // SOLO ADMIN puede actualizar cantidades
            if ($user['rol'] != 1) {
                http_response_code(403);
                echo json_encode(['success' => false, 'error' => 'Se requiere rol de administrador']);
                break;
            }
            $controller = new TicketsController();
            $controller->updateApprovedQuantities();
            break;
            

    }
    break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
}
?>
