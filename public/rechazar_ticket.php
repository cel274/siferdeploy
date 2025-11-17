<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['id']) || $_SESSION['rol'] != 1) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['rechazar_ticket'])) {
    $ticket_id = $_POST['ticket_id'];
    $usuario_id = $_SESSION['id'];

    try {
        // Primero obtener el número de ticket para el mensaje
        $sqlTicket = "SELECT numero_ticket FROM tickets WHERE idTicket = ?";
        $stmtTicket = $pdo->prepare($sqlTicket);
        $stmtTicket->execute([$ticket_id]);
        $ticket = $stmtTicket->fetch();

        $sqlRechazar = "UPDATE tickets SET 
                       estado = 'rechazado', 
                       fecha_aprobacion = NOW(), 
                       aprobado_por = ? 
                       WHERE idTicket = ? AND estado = 'pendiente'";
        $stmtRechazar = $pdo->prepare($sqlRechazar);
        $stmtRechazar->execute([$usuario_id, $ticket_id]);

        if ($stmtRechazar->rowCount() > 0) {
            $_SESSION['success'] = "✅ Ticket #" . $ticket['numero_ticket'] . " rechazado correctamente";
        } else {
            $_SESSION['error'] = "❌ No se pudo rechazar el ticket";
        }

    } catch (Exception $e) {
        $_SESSION['error'] = "❌ Error al rechazar ticket: " . $e->getMessage();
    }

    header("Location: tickets.php");
    exit();
}
