<?php
require_once __DIR__ . '/../models/Usuario.php';

class AuthController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function register() {
    try {
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['nombre']) || !isset($data['contraseña']) || !isset($data['rol'])) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
            return;
        }

        $nombre = trim($data['nombre']);
        $contraseña = $data['contraseña'];
        $rol = intval($data['rol']);

        if (empty($nombre) || empty($contraseña)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'Nombre y contraseña son requeridos']);
            return;
        }

        if (strlen($contraseña) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 6 caracteres']);
            return;
        }

        $result = $this->authModel->register($nombre, $contraseña, $rol);

        if ($result['success']) {
            http_response_code(201);
            echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente', 'user_id' => $result['user_id']]);
        } else {
            http_response_code(400);
            echo json_encode(['success' => false, 'error' => $result['error']]);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
    }
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