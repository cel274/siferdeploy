<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header('Location: index.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombreProducto = trim($_POST['nombreProducto']);
    $descripcion = trim($_POST['descripcion']);
    $cantidad = intval($_POST['cantidad']);

    if (strlen($nombreProducto) < 2 || $cantidad < 0) {
        $_SESSION['admin_error'] = "Datos del producto inválidos.";
        header("Location: admin.php");
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO productos (nombreProducto, descripcion, cantidad) VALUES (?, ?, ?)");
        $stmt->execute([$nombreProducto, $descripcion, $cantidad]);

        $_SESSION['admin_success'] = "Producto agregado correctamente.";
        header("Location: admin.php");
        exit();

    } catch (PDOException $e) {
        $_SESSION['admin_error'] = "Error al agregar producto: " . $e->getMessage();
        header("Location: admin.php");
        exit();
    }
}
?>