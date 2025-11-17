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

        // Obtener los items del ticket
        $sqlItems = "SELECT ti.*, p.nombre as producto_nombre, p.cantidad as stock_actual 
                    FROM ticket_items ti 
                    JOIN productos p ON ti.producto_id = p.idProducto 
                    WHERE ti.ticket_id = ?";
        $stmtItems = $pdo->prepare($sqlItems);
        $stmtItems->execute([$ticket_id]);
        $items = $stmtItems->fetchAll();

        // Verificar stock disponible antes de aprobar
        foreach ($items as $item) {
            if ($item['cantidad_solicitada'] > $item['stock_actual']) {
                throw new Exception("Stock insuficiente para: " . $item['producto_nombre'] . 
                                  ". Solicitado: " . $item['cantidad_solicitada'] . 
                                  ", Disponible: " . $item['stock_actual']);
            }
        }

        // Actualizar ticket a aprobado
        $sqlAprobar = "UPDATE tickets SET 
                      estado = 'aprobado', 
                      fecha_aprobacion = NOW(), 
                      aprobado_por = ? 
                      WHERE id = ? AND estado = 'pendiente'";
        $stmtAprobar = $pdo->prepare($sqlAprobar);
        $stmtAprobar->execute([$usuario_id, $ticket_id]);

        if ($stmtAprobar->rowCount() == 0) {
            throw new Exception("Ticket no encontrado o ya fue procesado");
        }

        // Actualizar items con cantidad aprobada y descontar stock
        $sqlUpdateItem = "UPDATE ticket_items SET cantidad_aprobada = cantidad_solicitada WHERE ticket_id = ?";
        $stmtUpdateItem = $pdo->prepare($sqlUpdateItem);
        $stmtUpdateItem->execute([$ticket_id]);

        // Descontar del inventario
        $sqlUpdateStock = "UPDATE productos p 
                          JOIN ticket_items ti ON p.idProducto = ti.producto_id 
                          SET p.cantidad = p.cantidad - ti.cantidad_solicitada 
                          WHERE ti.ticket_id = ?";
        $stmtUpdateStock = $pdo->prepare($sqlUpdateStock);
        $stmtUpdateStock->execute([$ticket_id]);

        $pdo->commit();
        $_SESSION['success'] = "✅ Ticket aprobado y stock actualizado correctamente";

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['error'] = "❌ Error al aprobar ticket: " . $e->getMessage();
    }

    header("Location: tickets.php");
    exit();
}
