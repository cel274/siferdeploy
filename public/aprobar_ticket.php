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

    // Obtener los items del ticket para actualizar stock
    $sql_items = "SELECT producto_id, cantidad_solicitada FROM ticket_items WHERE ticket_id = ?";
    $stmt_items = $pdo->prepare($sql_items);
    $stmt_items->execute([$ticket_id]);
    $items = $stmt_items->fetchAll();

    // Actualizar stock y cantidades aprobadas
    foreach ($items as $item) {
        // Verificar que hay stock suficiente
        $sql_stock = "SELECT cantidad FROM productos WHERE idProducto = ?";
        $stmt_stock = $pdo->prepare($sql_stock);
        $stmt_stock->execute([$item['producto_id']]);
        $stock_actual = $stmt_stock->fetchColumn();

        if ($stock_actual >= $item['cantidad_solicitada']) {
            // Restar del stock
            $sql_update_stock = "UPDATE productos SET cantidad = cantidad - ? WHERE idProducto = ?";
            $stmt_update = $pdo->prepare($sql_update_stock);
            $stmt_update->execute([$item['cantidad_solicitada'], $item['producto_id']]);

            // Actualizar cantidad aprobada
            $sql_update_aprobada = "UPDATE ticket_items SET cantidad_aprobada = ? WHERE ticket_id = ? AND producto_id = ?";
            $stmt_aprobada = $pdo->prepare($sql_update_aprobada);
            $stmt_aprobada->execute([$item['cantidad_solicitada'], $ticket_id, $item['producto_id']]);
        } else {
            throw new Exception("Stock insuficiente para el producto ID: " . $item['producto_id']);
        }
    }

    // Actualizar estado del ticket
    $sql_ticket = "UPDATE tickets SET estado = 'aprobado', fecha_aprobacion = NOW(), aprobado_por = ? WHERE idTicket = ?";
    $stmt_ticket = $pdo->prepare($sql_ticket);
    $stmt_ticket->execute([$admin_id, $ticket_id]);

    $pdo->commit();

    $_SESSION['ticket_success'] = "✅ Ticket aprobado exitosamente";
    header("Location: detalle_ticket.php?id=" . $ticket_id);
    exit();

} catch (Exception $e) {
    $pdo->rollBack();
    $_SESSION['ticket_error'] = "❌ Error al aprobar el ticket: " . $e->getMessage();
    header("Location: detalle_ticket.php?id=" . $ticket_id);
    exit();
}
?>