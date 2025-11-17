<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['nombre'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_GET['id'])) {
    header('Location: tickets.php');
    exit();
}

$ticket_id = intval($_GET['id']);
$usuario_id = $_SESSION['id'];

if ($_SESSION['rol'] != 1) {
    $stmt = $pdo->prepare("SELECT idTicket FROM tickets WHERE idTicket = ? AND usuario_solicitante = ?");
    $stmt->execute([$ticket_id, $usuario_id]);
    $ticket_pertenece = $stmt->fetch();
    
    if (!$ticket_pertenece) {
        $_SESSION['ticket_error'] = "No tienes permisos para ver este ticket";
        header("Location: tickets.php");
        exit();
    }
}

$sql_ticket = "SELECT t.*, u.nombre as usuario_nombre, a.nombre as aprobador_nombre 
               FROM tickets t 
               LEFT JOIN usuarios u ON t.usuario_solicitante = u.id 
               LEFT JOIN usuarios a ON t.aprobado_por = a.id 
               WHERE t.idTicket = ?";
$stmt_ticket = $pdo->prepare($sql_ticket);
$stmt_ticket->execute([$ticket_id]);
$ticket = $stmt_ticket->fetch();

if (!$ticket) {
    $_SESSION['ticket_error'] = "Ticket no encontrado";
    header("Location: tickets.php");
    exit();
}

$sql_items = "SELECT ti.*, p.nombreProducto, p.descripcion 
              FROM ticket_items ti 
              JOIN productos p ON ti.producto_id = p.idProducto 
              WHERE ti.ticket_id = ?";
$stmt_items = $pdo->prepare($sql_items);
$stmt_items->execute([$ticket_id]);
$items = $stmt_items->fetchAll();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <title>Detalle del Ticket - SIFER</title>
    <style>
        .detalle-container {
            padding: 20px;
            max-width: 1000px;
            margin: 0 auto;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            height: fit-content;
        }
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        .info-item {
            margin-bottom: 10px;
        }
        .info-label {
            font-weight: bold;
            color: #555;
        }
        .info-value {
            color: #333;
        }
        .estado-pendiente { color: #ffc107; font-weight: bold; }
        .estado-aprobado { color: #28a745; font-weight: bold; }
        .estado-rechazado { color: #dc3545; font-weight: bold; }
        .estado-entregado { color: #17a2b8; font-weight: bold; }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table th, .table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .table th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 5px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-secondary {
            background: #6c757d;
            color: white;
        }
        .observaciones {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <nav class="sidebar close">
        <header>
            <div class="image-text">
                <span class="image">
                    <a href="index.php"><img src="./imgs/logo.png" alt="logo"></a>
                </span>
                <div class="text logo-text">
                    <span class="name" id="nombre">
                        <?php echo htmlspecialchars($_SESSION['nombre']); ?>
                    </span>
                    <span class="profession">7°4° 2025</span>
                </div>
            </div>
            <i class='bx bx-chevron-right toggle'></i>
        </header>

        <div class="menu-bar">
            <div class="menu">
                <li class="search-box">
                    <i class='bx bx-search-alt icon'></i>
                    <input type="text" placeholder="Buscar...">
                </li>

                <ul class="menu-links">
                    <li class="nav-link">
                        <a href="./index.php">
                            <i class='bx bx-home-alt-2 icon' ></i>
                            <span class="text nav-text">Inicio</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="./stock.php">
                            <i class='bx bx-bar-chart-square icon' ></i>
                            <span class="text nav-text">Stock</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="./tickets.php">
                            <i class='bx bx-clipboard icon'></i>
                            <span class="text nav-text">Tickets</span>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1): ?>
                    <li class="nav-link">
                        <a href="./admin.php">
                            <i class='bx bx-list-ul-square icon'></i>
                            <span class="text nav-text">Administración</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="bottom-content">
                <li class="">
                    <a href="logout.php">
                        <i class='bx bx-user-hexagon icon' ></i>
                        <span class="text nav-text">Cerrar Sesión</span>
                    </a>
                </li>

            </div>
        </div>
    </nav>

    <section class="home">
        <section class="banner">
            <h1 class="texto" style="text-shadow: #00000071 1px 0 10px;">Detalle del Ticket</h1>
        </section>

        <div class="detalle-container">
            <?php if (isset($_SESSION['ticket_error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['ticket_error']; unset($_SESSION['ticket_error']); ?>
                </div>
            <?php endif; ?>

            <div class="card">
                <div style="display: flex; justify-content: between; align-items: center; margin-bottom: 20px;">
                    <h2>Ticket: <?php echo htmlspecialchars($ticket['numero_ticket']); ?></h2>
                    <a href="tickets.php" class="btn btn-secondary">← Volver a Tickets</a>
                </div>

                <div class="info-grid">
    <div class="info-item">
        <div class="info-label">Solicitante:</div>
        <div class="info-value"><?php echo htmlspecialchars($ticket['usuario_nombre']); ?></div>
    </div>
    <div class="info-item">
        <div class="info-label">Estado:</div>
        <div class="info-value estado-<?php echo $ticket['estado']; ?>">
            <?php echo ucfirst($ticket['estado']); ?>
        </div>
    </div>
    <div class="info-item">
        <div class="info-label">Tipo de Material:</div>
        <div class="info-value">
            <?php 
            $iconos = [
                'Herramientas' => '🛠️',
                'Materiales' => '📦', 
                'Medición' => '📏'
            ];
            $tipo = $ticket['tipo_solicitud'] ?? '';
            if ($tipo && isset($iconos[$tipo])) {
                echo $iconos[$tipo] . ' ' . htmlspecialchars($tipo);
            } elseif ($tipo) {
                echo htmlspecialchars($tipo);
            } else {
                echo '<span style="color: #6c757d;">No especificado</span>';
            }
            ?>
        </div>
    </div>
    <div class="info-item">
        <div class="info-label">Fecha de Solicitud:</div>
        <div class="info-value">
            <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_solicitud'])); ?>
        </div>
    </div>
    <?php if ($ticket['fecha_aprobacion']): ?>
    <div class="info-item">
        <div class="info-label">Fecha de Aprobación:</div>
        <div class="info-value">
            <?php echo date('d/m/Y H:i', strtotime($ticket['fecha_aprobacion'])); ?>
        </div>
    </div>
    <div class="info-item">
        <div class="info-label">Aprobado por:</div>
        <div class="info-value"><?php echo htmlspecialchars($ticket['aprobador_nombre']); ?></div>
    </div>
    <?php endif; ?>
</div>
                <?php if (!empty($ticket['observaciones'])): ?>
                <div class="observaciones">
                    <div class="info-label">Observaciones:</div>
                    <div class="info-value"><?php echo nl2br(htmlspecialchars($ticket['observaciones'])); ?></div>
                </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <h3>📦 Materiales Solicitados</h3>
                <?php if (count($items) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Descripción</th>
                            <th>Cantidad Solicitada</th>
                            <th>Cantidad Aprobada</th>
                            <th>Cantidad Entregada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($items as $item): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['nombreProducto']); ?></td>
                            <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                            <td><?php echo $item['cantidad_solicitada']; ?></td>
                            <td>
                                <?php if ($item['cantidad_aprobada'] > 0): ?>
                                    <?php echo $item['cantidad_aprobada']; ?>
                                <?php else: ?>
                                    <span style="color: #6c757d;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($item['cantidad_entregada'] > 0): ?>
                                    <?php echo $item['cantidad_entregada']; ?>
                                <?php else: ?>
                                    <span style="color: #6c757d;">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No hay materiales en este ticket.</p>
                <?php endif; ?>
            </div>

         <?php if ($_SESSION['rol'] == 1): ?>
<div class="card">
    <h3>⚡ Acciones de Administrador</h3>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php if ($ticket['estado'] == 'pendiente'): ?>
            <button onclick="confirmarAccion('aprobar', <?php echo $ticket_id; ?>)" class="btn btn-success">✅ Aprobar Ticket</button>
            <button onclick="confirmarAccion('rechazar', <?php echo $ticket_id; ?>)" class="btn" style="background: #dc3545; color: white;">❌ Rechazar Ticket</button>
        <?php elseif ($ticket['estado'] == 'aprobado'): ?>
            <button onclick="confirmarAccion('entregar', <?php echo $ticket_id; ?>)" class="btn" style="background: #17a2b8; color: white;">📦 Marcar como Entregado</button>
        <?php endif; ?>
        
        <?php if ($ticket['estado'] == 'rechazado' || $ticket['estado'] == 'entregado'): ?>
            <button onclick="confirmarAccion('reactivar', <?php echo $ticket_id; ?>)" class="btn" style="background: #ffc107; color: black;">🔄 Reactivar Ticket</button>
        <?php endif; ?>
    </div>
</div>

<script>
function confirmarAccion(accion, ticketId) {
    const mensajes = {
        'aprobar': '¿Estás seguro de que querés aprobar este ticket? Se descontará el stock.',
        'rechazar': '¿Estás seguro de que querés rechazar este ticket?',
        'entregar': '¿Estás seguro de que querés marcar este ticket como entregado?',
        'reactivar': '¿Estás seguro de que querés reactivar este ticket?'
    };

    if (confirm(mensajes[accion])) {
        window.location.href = accion + '_ticket.php?id=' + ticketId;
    }
}
</script>
<?php endif; ?>
</body>
</html>