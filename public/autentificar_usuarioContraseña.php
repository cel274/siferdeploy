<?php
session_start();
require 'sifer_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['usuario']);
    $password = trim($_POST['contraseña']);

    try {
        $stmt = $pdo->prepare("SELECT id, nombre, contraseña, rol FROM usuarios WHERE nombre = ?");
        $stmt->execute([$username]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            error_log("Usuario encontrado: " . $usuario['nombre']);
            error_log("Hash en BD: " . $usuario['contraseña']);
            error_log("Contraseña ingresada: " . $password);
            
            if (password_verify($password, $usuario['contraseña'])) {
                $_SESSION['id'] = $usuario['id'];
                $_SESSION['nombre'] = $usuario['nombre'];
                $_SESSION['rol'] = $usuario['rol'];
                
                error_log("Login EXITOSO - Usuario: " . $usuario['nombre']);
                header("Location: index.php");
                exit();
            } else {
                error_log("Contraseña INCORRECTA para usuario: " . $username);
                $_SESSION['login_error'] = "Contraseña incorrecta.";
                header("Location: login.php");
                exit();
            }
        } else {
            error_log("Usuario NO encontrado: " . $username);
            $_SESSION['login_error'] = "Usuario no encontrado.";
            header("Location: login.php");
            exit();
        }
    } catch (PDOException $e) {
        error_log("Error PDO: " . $e->getMessage());
        $_SESSION['login_error'] = "Error de sistema. Intente nuevamente.";
        header("Location: login.php");
        exit();
    }
} else {
    header("Location: login.php");
    exit();
}
?>
