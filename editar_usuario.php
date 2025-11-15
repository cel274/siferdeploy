<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = intval($_POST['id']);
    $nombre = trim($_POST['nombre']);
    $rol = intval($_POST['rol']);

    try {
        $check = $pdo->prepare("SELECT id FROM usuarios WHERE id = ?");
        $check->execute([$id]);
        
        if (!$check->fetch()) {
            $_SESSION['admin_error'] = "Usuario no encontrado.";
            header("Location: admin.php");
            exit();
        }

        $stmt = $pdo->prepare("UPDATE usuarios SET nombre = ?, rol = ? WHERE id = ?");
        $stmt->execute([$nombre, $rol, $id]);

        $_SESSION['admin_success'] = "Usuario actualizado correctamente.";
        header("Location: admin.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['admin_error'] = "Error al actualizar usuario: " . $e->getMessage();
        header("Location: admin.php");
        exit();
    }
}
?>