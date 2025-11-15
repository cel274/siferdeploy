<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $contraseña = $_POST['contraseña'];
    $rol = intval($_POST['rol']);

    if (strlen($nombre) < 3 || strlen($contraseña) < 4) {
        $_SESSION['admin_error'] = "Usuario o contraseña demasiado cortos.";
        header("Location: admin.php");
        exit();
    }

    try {
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE nombre = ?");
        $check->execute([$nombre]);
        
        if ($check->fetch()) {
            $_SESSION['admin_error'] = "El nombre de usuario ya existe.";
            header("Location: admin.php");
            exit();
        }

        $hashed = password_hash($contraseña, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre, contraseña, rol) VALUES (?, ?, ?)");
        $stmt->execute([$nombre, $hashed, $rol]);

        $_SESSION['admin_success'] = "Usuario agregado correctamente.";
        header("Location: admin.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['admin_error'] = "Error al agregar usuario: " . $e->getMessage();
        header("Location: admin.php");
        exit();
    }
}
?>