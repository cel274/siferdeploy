<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $idProducto = intval($_POST['idProducto']);
    $nombreProducto = trim($_POST['nombreProducto']);
    $descripcion = trim($_POST['descripcion']);
    $cantidad = intval($_POST['cantidad']);

    if (strlen($nombreProducto) < 2 || $cantidad < 0) {
        $_SESSION['admin_error'] = "Datos del producto inválidos.";
        header("Location: admin.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("UPDATE productos SET nombreProducto = ?, descripcion = ?, cantidad = ? WHERE idProducto = ?");
        $stmt->execute([$nombreProducto, $descripcion, $cantidad, $idProducto]);

        $_SESSION['admin_success'] = "Producto actualizado correctamente.";
        header("Location: admin.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['admin_error'] = "Error al actualizar producto: " . $e->getMessage();
        header("Location: admin.php");
        exit();
    }
}
?>