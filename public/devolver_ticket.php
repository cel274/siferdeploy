<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] != 1) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['devolver_ticket'])) {
    $ticket_id = $_POST['ticket_id'];
    $usuario_id = $_SESSION['id'];

    try {
        $pdo->beginTransaction();

        $sqlCheck = "SELECT * FROM tickets WHERE idTicket = ? AND estado = 'aprobado'";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$ticket_id]);
        $ticket = $stmtCheck->fetch();

        if (!$ticket) {
            throw new Exception("Ticket no encontrado o no está aprobado");
        }

        $sqlReturnStock = "UPDATE productos p 
                          JOIN ticket_items ti ON p.idProducto = ti.producto_id 
                          SET p.cantidad = p.cantidad + ti.cantidad_aprobada 
                          WHERE ti.ticket_id = ?";
        $stmtReturnStock = $pdo->prepare($sqlReturnStock);
        $stmtReturnStock->execute([$ticket_id]);

        $sqlDevolver = "UPDATE tickets SET 
                       fecha_devolucion = NOW(), 
                       devuelto_por = ?, 
                       estado_devolucion = 'completada' 
                       WHERE idTicket = ?";
        $stmtDevolver = $pdo->prepare($sqlDevolver);
        $stmtDevolver->execute([$usuario_id, $ticket_id]);

        $pdo->commit();
        $_SESSION['success'] = "Devolución completada y stock repuesto correctamente";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error en devolución: " . $e->getMessage();
    }

    header("Location: tickets.php");
    exit();
}
