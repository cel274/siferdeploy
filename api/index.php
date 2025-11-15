<?php
// Configuración de rutas para local y producción
$base_path = dirname(__DIR__);

// Incluir CORS - rutas corregidas
require_once $base_path . '/cors.php';
require_once $base_path . '/api/config/database.php';

// Incluir controladores
require_once $base_path . '/api//controllers/AuthController.php';
require_once $base_path . '/api//controllers/ProductosController.php';
require_once $base_path . '/api//controllers/TicketsController.php';
require_once $base_path . '/api//controllers/UsuariosController.php';

header('Content-Type: application/json');

// Obtener la ruta real de la API
$request_uri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

// Extraer el path de la API (remover /api/)
$path = parse_url($request_uri, PHP_URL_PATH);
$api_path = str_replace('/api', '', $path);
$api_path = rtrim($api_path, '/');

// Si el path está vacío después de remover /api, es la raíz de la API
if ($api_path === '') {
    $api_path = '/';
}

// Debug
error_log("API Request: $method $api_path");

// Routing de la API
try {
    switch (true) {
        // Ruta raíz de la API
        case $api_path === '/' && $method === 'GET':
            echo json_encode([
                'success' => true,
                'message' => 'ok'
            ]);
            break;

        // Autenticación
        case $api_path === '/login' && $method === 'POST':
            $controller = new AuthController();
            $controller->login();
            break;

        // Productos
        case $api_path === '/productos' && $method === 'GET':
            $controller = new ProductosController();
            $controller->getAll();
            break;

        // Usuarios
        case $api_path === '/usuarios' && $method === 'GET':
            $controller = new UsuariosController();
            $controller->getAll();
            break;

        // Tickets
        case $api_path === '/tickets' && $method === 'GET':
            $controller = new TicketsController();
            $controller->getAll();
            break;

        // Tickets por usuario
        case preg_match('#^/tickets/usuario/(\d+)$#', $api_path, $matches) && $method === 'GET':
            $controller = new TicketsController();
            $controller->getByUser($matches[1]);
            break;

        // Usuario por ID
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
    echo json_encode([
        'success' => false,
        'error' => 'Error interno: ' . $e->getMessage()
    ]);
}
?>