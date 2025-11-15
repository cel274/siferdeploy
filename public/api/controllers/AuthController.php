<?php
require_once __DIR__ . '/../models/Usuario.php';

class JWT_Auth {
    private static $secret_key = 'teamotomotakino';
    
    public static function encode($payload) {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
        $payload = json_encode($payload);
        
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
        
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, self::$secret_key, true);
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
        
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }
    
    public static function decode($jwt) {
        $tokenParts = explode('.', $jwt);
        if (count($tokenParts) !== 3) return false;
        
        list($header, $payload, $signature) = $tokenParts;
        
        $validSignature = hash_hmac('sha256', $header . "." . $payload, self::$secret_key, true);
        $base64UrlValidSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($validSignature));
        
        if ($signature !== $base64UrlValidSignature) return false;
        
        return json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $payload)), true);
    }
}

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function login() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['nombre']) || !isset($data['contraseña'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios']);
                return;
            }

            $user = $this->usuarioModel->login($data['nombre'], $data['contraseña']);

            if ($user) {
                $payload = [
                    'user_id' => $user['id'],
                    'username' => $user['nombre'],
                    'rol' => $user['rol'],
                    'exp' => time() + (24 * 60 * 60)
                ];
                
                $token = JWT_Auth::encode($payload);
                
                echo json_encode([
                    'success' => true,
                    'user' => $user,
                    'token' => $token,
                    'message' => 'Login exitoso'
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Credenciales incorrectas'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error del servidor: ' . $e->getMessage()
            ]);
        }
    }
}
?>