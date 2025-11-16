<?php
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
            
            error_log("Login attempt - Data: " . print_r($data, true));
            
            if (!isset($data['nombre']) || !isset($data['contraseña'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Faltan campos obligatorios'
                ]);
                return;
            }

            $nombre = trim($data['nombre']);
            $contraseña = trim($data['contraseña']);

            // Usar el método del modelo para verificar credenciales
            $user = $this->usuarioModel->verifyCredentials($nombre, $contraseña);

            if ($user) {
                // Generar token simple (puedes implementar JWT después)
                $token = bin2hex(random_bytes(32));
                
                // Estructura que espera Android
                $response = [
                    'success' => true,
                    'message' => 'Login exitoso',
                    'user' => [
                        'id' => (int)$user['id'],
                        'nombre' => $user['nombre'],
                        'rol' => (int)$user['rol'],
                        'rol_nombre' => $user['rol'] == 1 ? 'Administrador' : 'Usuario'
                    ],
                    'token' => $token
                ];
                
                error_log("Login success: " . json_encode($response));
                echo json_encode($response);
            } else {
                http_response_code(401);
                echo json_encode([
                    'success' => false,
                    'error' => 'Credenciales incorrectas'
                ]);
            }
        } catch (Exception $e) {
            error_log("Login error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error del servidor: ' . $e->getMessage()
            ]);
        }
    }

    public function register() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['nombre']) || !isset($data['contraseña'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Datos incompletos'
                ]);
                return;
            }

            $nombre = trim($data['nombre']);
            $contraseña = trim($data['contraseña']);
            $rol = 2;

            if (empty($nombre) || empty($contraseña)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'error' => 'Nombre y contraseña son requeridos'
                ]);
                return;
            }

            if (strlen($contraseña) < 4) {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'error' => 'La contraseña debe tener al menos 4 caracteres'
                ]);
                return;
            }

            $result = $this->usuarioModel->createUser($nombre, $contraseña, $rol);

            if ($result['success']) {
                echo json_encode([
                    'success' => true, 
                    'message' => 'Usuario registrado exitosamente',
                    'user' => [
                        'id' => (int)$result['user_id'],
                        'nombre' => $nombre,
                        'rol' => $rol,
                        'rol_nombre' => 'Usuario'
                    ]
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false, 
                    'error' => $result['error']
                ]);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'error' => 'Error interno: ' . $e->getMessage()
            ]);
        }
    }
}
?>
