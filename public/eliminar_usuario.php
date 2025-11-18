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
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM tickets WHERE usuario_solicitante = ?");
        $stmt->execute([$id]);
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $pdo->commit();

        if ($stmt->rowCount() > 0) {
            $_SESSION['admin_success'] = "Usuario y sus tickets eliminados correctamente.";
        } else {
            $_SESSION['admin_error'] = "Usuario no encontrado.";
        }

        header("Location: admin.php");
        exit();

    } catch (PDOException $e) {
        $pdo->rollBack();
        $_SESSION['admin_error'] = "Error al eliminar usuario: " . $e->getMessage();
        header("Location: admin.php");
        exit();
    }
}
?>