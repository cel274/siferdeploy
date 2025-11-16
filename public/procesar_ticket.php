
<?php
require 'sifer_db.php';

if (!isset($_SESSION['nombre']) || !isset($_SESSION['id'])) {
    header('Location: login.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_id = $_SESSION['id'];
    $es_admin = ($_SESSION['rol'] == 1);
    $tipo_solicitud = trim($_POST['tipo_solicitud']);
    $observaciones = trim($_POST['observaciones'] ?? '');
    $productos = $_POST['productos'];

    try {
        $year = date('Y');
        $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tickets WHERE YEAR(fecha_solicitud) = ?");
        $stmt->execute([$year]);
        $result = $stmt->fetch();
        $consecutivo = $result['total'] + 1;
        $numeroTicket = "TKT" . $year . str_pad($consecutivo, 5, '0', STR_PAD_LEFT);

        $pdo->beginTransaction();

        if ($es_admin) {
            $estado = 'aprobado';
            $fecha_aprobacion = date('Y-m-d H:i:s');
            $aprobado_por = $usuario_id;
        } else {
            $estado = 'pendiente';
            $fecha_aprobacion = null;
            $aprobado_por = null;
        }

        $sqlTicket = "INSERT INTO tickets (numero_ticket, usuario_solicitante, tipo_solicitud, observaciones, estado, fecha_aprobacion, aprobado_por) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmtTicket = $pdo->prepare($sqlTicket);
        $stmtTicket->execute([$numeroTicket, $usuario_id, $tipo_solicitud, $observaciones, $estado, $fecha_aprobacion, $aprobado_por]);
        $ticketId = $pdo->lastInsertId();

        $sqlItem = "INSERT INTO ticket_items (ticket_id, producto_id, cantidad_solicitada, cantidad_aprobada) VALUES (?, ?, ?, ?)";
        $stmtItem = $pdo->prepare($sqlItem);

        foreach ($productos as $item) {
            if (!empty($item['id']) && !empty($item['cantidad']) && $item['cantidad'] > 0) {
                $cantidad_aprobada = $es_admin ? $item['cantidad'] : 0;
                $stmtItem->execute([$ticketId, $item['id'], $item['cantidad'], $cantidad_aprobada]);
                
                if ($es_admin) {
                    $sql_update_stock = "UPDATE productos SET cantidad = cantidad - ? WHERE idProducto = ?";
                    $stmt_update = $pdo->prepare($sql_update_stock);
                    $stmt_update->execute([$item['cantidad'], $item['id']]);
                }
            }
        }

        $pdo->commit();
        
        if ($es_admin) {
            $_SESSION['ticket_success'] = "✅ Solicitud procesada y aprobada automáticamente: " . $numeroTicket;
        } else {
            $_SESSION['ticket_success'] = "✅ Solicitud creada exitosamente: " . $numeroTicket . " (Esperando aprobación)";
        }
        
        header("Location: tickets.php");
        exit();

    } catch (Exception $e) {
        $pdo->rollBack();
        $_SESSION['ticket_error'] = "❌ Error al crear la solicitud: " . $e->getMessage();
        header("Location: tickets.php");
        exit();
    }
} else {
    header("Location: tickets.php");
    exit();
}
?>