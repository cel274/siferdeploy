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
    $pdo->beginTransaction();

    $sql_items = "SELECT producto_id, cantidad_aprobada FROM ticket_items WHERE ticket_id = ? AND cantidad_aprobada > 0";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->execute([$ticket_id]);
    $items = $stmt_items->fetchAll();

    foreach ($items as $item) {
        $sql_update_entregada = "UPDATE ticket_items SET cantidad_entregada = ? WHERE ticket_id = ? AND producto_id = ?";
        $stmt_entregada = $pdo->prepare($sql_update_entregada);
        $stmt_entregada->execute([$item['cantidad_aprobada'], $ticket_id, $item['producto_id']]);
    }

    $sql_ticket = "UPDATE tickets SET estado = 'entregado', fecha_entrega = NOW() WHERE idTicket = ?";
    $stmt_ticket = $pdo->prepare($sql_ticket);
    $stmt_ticket->execute([$ticket_id]);

    $pdo->commit();

    $_SESSION['ticket_success'] = "Ticket marcado como entregado exitosamente";
    header("Location: detalle_ticket.php?id=" . $ticket_id);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['ticket_error'] = "Error al marcar como entregado: " . $e->getMessage();
    header("Location: detalle_ticket.php?id=" . $ticket_id);
    exit();
}
?>
