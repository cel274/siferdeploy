<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);


require_once 'cors.php';


require_once 'controllers/AuthController.php';
require_once 'controllers/ProductosController.php';
require_once 'controllers/TicketsController.php';
require_once 'controllers/UsuariosController.php';

header('Content-Type: application/json');


$method = $_SERVER['REQUEST_METHOD'];


if (isset($_GET['path'])) {
    $path = '/' . $_GET['path'];
} else {
    $request_uri = $_SERVER['REQUEST_URI'];
    $path = parse_url($request_uri, PHP_URL_PATH);

    $base_path = '/api';
    if (strpos($path, $base_path) === 0) {
        $path = substr($path, strlen($base_path));
    }
}


if ($path === '' || $path === '/') {
    $path = '/';
}


error_log("=== DEBUG ROUTING ===");
error_log("Method: $method");
error_log("Path: $path");
error_log("GET params: " . print_r($_GET, true));
error_log("=====================");

try {
    switch (true) {

        case $path === '/' && $method === 'GET':
            echo json_encode([
                'success' => true,
                'message' => 'acá no hay nada poné un endpoint'
            ]);
            break;


        case $path === '/login' && $method === 'POST':
            $controller = new AuthController();
            $controller->login();
            break;


        case $path === '/productos' && $method === 'GET':
            $controller = new ProductosController();
            $controller->getAll();
            break;

        case $path === '/productos/stock' && $method === 'PUT':
            $controller = new ProductosController();
            $controller->updateStock();
            break;


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


        case preg_match('#^/tickets/usuario/(\d+)$#', $path, $matches) && $method === 'GET':
            $controller = new TicketsController();
            $controller->getByUser($matches[1]);
            break;


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