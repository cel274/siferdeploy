<?php
// Mostrar todos los errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Incluir CORS primero
require_once 'cors.php';

// Incluir controladores
require_once 'controllers/AuthController.php';
require_once 'controllers/ProductosController.php';
require_once 'controllers/TicketsController.php';
require_once 'controllers/UsuariosController.php';

header('Content-Type: application/json');

// Obtener información de la solicitud
$method = $_SERVER['REQUEST_METHOD'];

// **NUEVO: Obtener la ruta del parámetro path o de REQUEST_URI**
if (isset($_GET['path'])) {
    $path = '/' . $_GET['path'];
} else {
    $request_uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($request_uri, PHP_URL_PATH);
    
    // Remover /api/ si existe
    $base_path = '/api';
    if (strpos($path, $base_path) === 0) {
        $path = substr($path, strlen($base_path));
    }
}

// Si el path está vacío, es la raíz
if ($path === '' || $path === '/') {
    $path = '/';
}

// Debug: Mostrar información de la ruta
error_log("=== DEBUG ROUTING ===");
error_log("Method: $method");
error_log("Path: $path");
error_log("GET params: " . print_r($_GET, true));
error_log("=====================");

// Routing
try {
    switch (true) {
        // Ruta raíz - GET
        case $path === '/' && $method === 'GET':
            echo json_encode([
                'success' => true,
                'message' => 'acá no hay nada poné un endpoint'
            ]);
            break;

        // Autenticación
        case $path === '/login' && $method === 'POST':
            $controller = new AuthController();
            $controller->login();
            break;

        // Productos
        case $path === '/productos' && $method === 'GET':
            $controller = new ProductosController();
            $controller->getAll();
            break;

        case $path === '/productos/stock' && $method === 'PUT':
            $controller = new ProductosController();
            $controller->updateStock();
            break;

        // Tickets
        case $path === '/tickets' && $method === 'GET':
            $controller = new TicketsController();
            $controller->getAll();
            break;

        case $path === '/tickets' && $method === 'POST':
            $controller = new TicketsController();
            $controller->create();
            break;

        case $path === '/tickets/estado' && $method === 'PUT':
            $controller = new TicketsController();
            $controller->updateStatus();
            break;

        // Tickets por usuario
        case preg_match('#^/tickets/usuario/(\d+)$#', $path, $matches) && $method === 'GET':
            $controller = new TicketsController();
            $controller->getByUser($matches[1]);
            break;

        // Usuarios
        case $path === '/usuarios' && $method === 'GET':
            $controller = new UsuariosController();
            $controller->getAll();
            break;

        case preg_match('#^/usuarios/(\d+)$#', $path, $matches) && $method === 'GET':
            $controller = new UsuariosController();
            $controller->getById($matches[1]);
            break;

        default:
            http_response_code(404);
            echo json_encode([
                'success' => false, 
                'error' => 'Endpoint no encontrado',
                'debug_info' => [
                    'requested_path' => $path,
                    'method' => $method
                ]
            ]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'error' => 'Error interno del servidor: ' . $e->getMessage()
    ]);
}
?>