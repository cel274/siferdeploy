<?php
class AuthController {
    private $db;

    public function __construct() {
        global $pdo;
        $this->db = $pdo;
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

            // Buscar usuario directamente en la BD
            $stmt = $this->db->prepare("SELECT id, nombre, contraseña, rol FROM usuarios WHERE nombre = ?");
            $stmt->execute([$nombre]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($contraseña, $user['contraseña'])) {
                // Login exitoso - estructura que espera Android
                $response = [
                    'success' => true,
                    'message' => 'Login exitoso',
                    'user' => [
                        'id' => (int)$user['id'],
                        'nombre' => $user['nombre'],
                        'rol' => (int)$user['rol'],
                        'rol_nombre' => $user['rol'] == 1 ? 'Administrador' : 'Usuario'
                    ],
                    'token' => bin2hex(random_bytes(32))
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
                echo json_encode(['success' => false, 'error' => 'Datos incompletos']);
                return;
            }

            $nombre = trim($data['nombre']);
            $contraseña = trim($data['contraseña']);
            $rol = 2;

            // Validaciones
            if (strlen($nombre) < 3) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El nombre debe tener al menos 3 caracteres']);
                return;
            }

            if (strlen($contraseña) < 4) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'La contraseña debe tener al menos 4 caracteres']);
                return;
            }

            // Verificar si usuario existe
            $checkStmt = $this->db->prepare("SELECT id FROM usuarios WHERE nombre = ?");
            $checkStmt->execute([$nombre]);
            
            if ($checkStmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El nombre de usuario ya existe']);
                return;
            }

            // Crear usuario
            $hashedPassword = password_hash($contraseña, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, contraseña, rol) VALUES (?, ?, ?)");
            $result = $stmt->execute([$nombre, $hashedPassword, $rol]);
            
            if ($result) {
                $userId = $this->db->lastInsertId();
                echo json_encode([
                    'success' => true, 
                    'message' => 'Usuario registrado exitosamente',
                    'user' => [
                        'id' => (int)$userId,
                        'nombre' => $nombre,
                        'rol' => $rol,
                        'rol_nombre' => 'Usuario'
                    ]
                ]);
            } else {
                http_response_code(500);
                echo json_encode(['success' => false, 'error' => 'Error al crear el usuario']);
            }

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
        }
    }
}
?>
