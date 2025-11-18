<?php
session_start();
require 'sifer_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['nombre'] ?? $_POST['usuario'] ?? '');
    $password = trim($_POST['contraseña']);

    if (empty($username) || empty($password)) {
        $_SESSION['login_error'] = "Usuario y contraseña son requeridos.";
        header("Location: login.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("SELECT id, nombre, contraseña, rol FROM usuarios WHERE nombre = ?");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            error_log("Usuario encontrado: " . $usuario['nombre']);
            error_log("Hash en BD: " . $usuario['contraseña']);
            
            if (password_verify($password, $usuario['contraseña'])) {
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
                
                error_log("Login EXITOSO - Usuario: " . $usuario['nombre']);

                if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => true,
                        'message' => 'Login exitoso',
                        'user' => [
                            'id' => $usuario['id'],
                            'nombre' => $usuario['nombre'],
                            'rol' => $usuario['rol'],
                            'rol_nombre' => $usuario['rol'] == 1 ? 'Administrador' : 'Usuario'
                        ],
                        'token' => bin2hex(random_bytes(16))
                    ]);
                    exit();
                } else {
                    header("Location: index.php");
                    exit();
                }
            } else {
                error_log("Contraseña INCORRECTA para usuario: " . $username);
                $errorMsg = "Contraseña incorrecta.";
                
                if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                    header('Content-Type: application/json');
                    echo json_encode([
                        'success' => false,
                        'error' => $errorMsg
                    ]);
                    exit();
                } else {
                    $_SESSION['login_error'] = $errorMsg;
                    header("Location: login.php");
                    exit();
                }
            }
        } else {
            error_log("Usuario NO encontrado: " . $username);
            $errorMsg = "Usuario no encontrado.";
            
            if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success' => false,
                    'error' => $errorMsg
                ]);
                exit();
            } else {
                $_SESSION['login_error'] = $errorMsg;
                header("Location: login.php");
                exit();
            }
        }
    } catch (PDOException $e) {
        error_log("Error PDO: " . $e->getMessage());
        $errorMsg = "Error de sistema. Intente nuevamente.";
        
        if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'error' => $errorMsg
            ]);
            exit();
        } else {
            $_SESSION['login_error'] = $errorMsg;
            header("Location: login.php");
            exit();
        }
    }
} else {
    if (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido'
        ]);
        exit();
    } else {
        header("Location: login.php");
        exit();
    }
}
?>
