<?php
class AuthController {
    private $db;

    public function __construct() {
        global $pdo;
        
        if (!isset($pdo)) {
            error_log("ERROR: $pdo no está disponible en AuthController");
            header('Content-Type: application/json');
            http_response_code(500);
            echo json_encode([
                'success' => false, 
                'error' => 'Error de configuración de base de datos'
            ]);
            exit();
        }
        
        $this->db = $pdo;
        error_log("DEBUG: Conexión a BD establecida en AuthController");
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
                    'error' => 'Faltan campos obligatorios: nombre y contraseña'
                ]);
                return;
            }

            $nombre = trim($data['nombre']);
            $contraseña = trim($data['contraseña']);

            if (empty($nombre) || empty($contraseña)) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Nombre y contraseña no pueden estar vacíos'
                ]);
                return;
            }

            $stmt = $this->db->prepare("SELECT id, nombre, contraseña, rol FROM usuarios WHERE nombre = ?");
            $stmt->execute([$nombre]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                error_log("Usuario encontrado: " . $user['nombre']);
                
                if (password_verify($contraseña, $user['contraseña'])) {
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
                    error_log("Contraseña incorrecta para usuario: " . $nombre);
                    http_response_code(401);
                    echo json_encode([
                        'success' => false,
                        'error' => 'Credenciales incorrectas'
                    ]);
                }
            } else {
                error_log("Usuario no encontrado: " . $nombre);
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

            $checkStmt = $this->db->prepare("SELECT id FROM usuarios WHERE nombre = ?");
            $checkStmt->execute([$nombre]);
            
            if ($checkStmt->fetch()) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'El nombre de usuario ya existe']);
                return;
            }

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
            error_log("Register error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'error' => 'Error interno: ' . $e->getMessage()]);
        }
    }
}
?>
