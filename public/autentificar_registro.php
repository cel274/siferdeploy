<?php
session_start();
require 'sifer_db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['usuario']);
    $password = ($_POST['contraseña']);
    $default_rol = 2;

    if(strlen($username) < 6 || strlen($password) < 6){
        $_SESSION['register_error'] = "Usuario o contraseña demasiado cortos.";
        header("Location: registro.php");
        exit();
    }

    $check = $pdo->prepare("SELECT id FROM usuarios WHERE nombre = ?");
    $check->bindParam(1, $username);
    $check->execute();
    $result = $check->fetch(PDO::FETCH_ASSOC);

    if($result){
        $_SESSION['register_error'] = "Ese nombre ya está ocupado.";
        header("Location: registro.php");
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, contraseña, rol) VALUES (?, ?, ?)");

    if($stmt->execute([$username, $hashed, $default_rol])){
        $_SESSION['register_success'] = "Registro exitoso. ¡Ya puedes iniciar sesión!";
        header("Location: login.php");
        exit();
    } else {
        $_SESSION['register_error'] = "Error al registrar el usuario.";
        header("Location: registro.php");
        exit();
    }
}
?>