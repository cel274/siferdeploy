<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header('Location: index.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);

        if ($stmt->rowCount() > 0) {
            $_SESSION['admin_success'] = "Usuario eliminado correctamente.";
        } else {
            $_SESSION['admin_error'] = "Usuario no encontrado.";
        }

        header("Location: admin.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['admin_error'] = "Error al eliminar usuario: " . $e->getMessage();
        header("Location: admin.php");
        exit();
    }
}
?>