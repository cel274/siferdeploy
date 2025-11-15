<?php
require_once __DIR__ . '/config/jwt.php';
require_once __DIR__ . '/../models/Usuario.php';

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
                
                $token = JWT::encode($payload);
                
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