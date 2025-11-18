<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] != 1) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aprobar_ticket'])) {
    $ticket_id = $_POST['ticket_id'];
    $usuario_id = $_SESSION['id'];

    try {
        $pdo->beginTransaction();

        $sqlCheck = "SELECT t.*, u.nombre as solicitante 
                    FROM tickets t 
                    JOIN usuarios u ON t.usuario_solicitante = u.id 
                    WHERE t.idTicket = ? AND t.estado = 'pendiente'";
        $stmtCheck = $pdo->prepare($sqlCheck);
        $stmtCheck->execute([$ticket_id]);
        $ticket = $stmtCheck->fetch();

        if (!$ticket) {
            throw new Exception("Ticket no encontrado o ya fue procesado");
        }

        $sqlItems = "SELECT ti.*, p.nombreProducto, p.cantidad as stock_actual 
                    FROM ticket_items ti 
                    JOIN productos p ON ti.producto_id = p.idProducto 
                    WHERE ti.ticket_id = ?";
        $stmtItems = $pdo->prepare($sqlItems);
        $stmtItems->execute([$ticket_id]);
        $items = $stmtItems->fetchAll();

        if (empty($items)) {
            throw new Exception("El ticket no tiene items");
        }

        $stockErrors = [];
        foreach ($items as $item) {
            if ($item['cantidad_solicitada'] > $item['stock_actual']) {
                $stockErrors[] = $item['nombreProducto'] . " (Solicitado: " . $item['cantidad_solicitada'] . ", Disponible: " . $item['stock_actual'] . ")";
            }
        }

        if (!empty($stockErrors)) {
            throw new Exception("Stock insuficiente: " . implode(", ", $stockErrors));
        }

        $sqlAprobar = "UPDATE tickets SET 
                      estado = 'aprobado', 
                      fecha_aprobacion = NOW(), 
                      aprobado_por = ? 
                      WHERE idTicket = ?";
        $stmtAprobar = $pdo->prepare($sqlAprobar);
        $stmtAprobar->execute([$usuario_id, $ticket_id]);

        $sqlUpdateItem = "UPDATE ticket_items SET cantidad_aprobada = cantidad_solicitada WHERE ticket_id = ?";
        $stmtUpdateItem = $pdo->prepare($sqlUpdateItem);
        $stmtUpdateItem->execute([$ticket_id]);

        $sqlUpdateStock = "UPDATE productos p 
                          JOIN ticket_items ti ON p.idProducto = ti.producto_id 
                          SET p.cantidad = p.cantidad - ti.cantidad_solicitada 
                          WHERE ti.ticket_id = ?";
        $stmtUpdateStock = $pdo->prepare($sqlUpdateStock);
        $stmtUpdateStock->execute([$ticket_id]);

        $pdo->commit();
        
        $_SESSION['success'] = "Ticket #" . $ticket['numero_ticket'] . " aprobado correctamente. Stock actualizado.";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "Error al aprobar ticket: " . $e->getMessage();
    }

    header("Location: tickets.php");
    exit();
} else {
    header("Location: tickets.php");
    exit();
}
