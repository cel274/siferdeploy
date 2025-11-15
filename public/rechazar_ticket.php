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
$admin_id = $_SESSION['id'];

try {

    $sql = "UPDATE tickets SET estado = 'rechazado', fecha_aprobacion = NOW(), aprobado_por = ? WHERE idTicket = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$admin_id, $ticket_id]);

    $_SESSION['ticket_success'] = "✅ Ticket rechazado exitosamente";
    header("Location: detalle_ticket.php?id=" . $ticket_id);
    exit();

} catch (Exception $e) {
    $_SESSION['ticket_error'] = "❌ Error al rechazar el ticket: " . $e->getMessage();
    header("Location: detalle_ticket.php?id=" . $ticket_id);
    exit();
}
?>