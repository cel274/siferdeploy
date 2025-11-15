<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['nombre']) || $_SESSION['rol'] != 1) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: tickets.php');
    exit();
}

$ticket_id = intval($_GET['id']);

try {

    $sql = "UPDATE tickets SET estado = 'pendiente', fecha_aprobacion = NULL, fecha_entrega = NULL, aprobado_por = NULL WHERE idTicket = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$ticket_id]);

    $_SESSION['ticket_success'] = "✅ Ticket reactivado exitosamente";
    header("Location: detalle_ticket.php?id=" . $ticket_id);
    exit();

} catch (Exception $e) {
    $_SESSION['ticket_error'] = "❌ Error al reactivar el ticket: " . $e->getMessage();
    header("Location: detalle_ticket.php?id=" . $ticket_id);
    exit();
}
?>