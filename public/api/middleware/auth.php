<?php
require_once __DIR__ . '/../config/jwt.php';

function authenticate() {
    $headers = getallheaders();
    $token = null;
    
    if (isset($headers['Authorization'])) {
        $authHeader = $headers['Authorization'];
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = $matches[1];
        }
    }
    
    if (!$token) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Token de autenticación requerido']);
        exit;
    }
    
    $payload = JWT::decode($token);
    if (!$payload) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Token inválido o expirado']);
        exit;
    }
    
    if ($payload['rol'] != 1) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'No estás autorizado']);
        exit;
    }
    
    return $payload;
}
?>