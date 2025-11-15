<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['nombre']) || $_SESSION['rol'] != 1) {
    header('Location: login.php');
    exit();
}


$estado = $_GET['estado'] ?? 'pendiente';
$estados_permitidos = ['pendiente', 'aprobado', 'rechazado', 'entregado', 'todos'];

if (!in_array($estado, $estados_permitidos)) {
    $estado = 'pendiente';
}


$sql = "SELECT t.*, u.nombre as usuario_nombre 
        FROM tickets t 
        JOIN usuarios u ON t.usuario_solicitante = u.id 
        WHERE u.rol = 2"; 

if ($estado != 'todos') {
    $sql .= " AND t.estado = ?";
}

$sql .= " ORDER BY t.fecha_solicitud DESC";

if ($estado != 'todos') {
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$estado]);
} else {
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
}

$tickets = $stmt->fetchAll();


$stmt_pendientes = $pdo->prepare("SELECT COUNT(*) as total FROM tickets t JOIN usuarios u ON t.usuario_solicitante = u.id WHERE t.estado = 'pendiente' AND u.rol = 2");
$stmt_pendientes->execute();
$pendientes = $stmt_pendientes->fetch()['total'];

$stmt_aprobados = $pdo->prepare("SELECT COUNT(*) as total FROM tickets t JOIN usuarios u ON t.usuario_solicitante = u.id WHERE t.estado = 'aprobado' AND u.rol = 2");
$stmt_aprobados->execute();
$aprobados = $stmt_aprobados->fetch()['total'];

$stmt_rechazados = $pdo->prepare("SELECT COUNT(*) as total FROM tickets t JOIN usuarios u ON t.usuario_solicitante = u.id WHERE t.estado = 'rechazado' AND u.rol = 2");
$stmt_rechazados->execute();
$rechazados = $stmt_rechazados->fetch()['total'];

$stmt_totales = $pdo->prepare("SELECT COUNT(*) as total FROM tickets t JOIN usuarios u ON t.usuario_solicitante = u.id WHERE u.rol = 2");
$stmt_totales->execute();
$totales = $stmt_totales->fetch()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <title>Solicitudes de Usuarios - SIFER</title>
    <style>
        .admin-container {
            padding: 20px;
            max-width: 1400px;
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
        .filtros {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .filtro-btn {
            padding: 8px 16px;
            border: 2px solid #007bff;
            border-radius: 20px;
            background: white;
            color: #007bff;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        .filtro-btn:hover, .filtro-btn.active {
            background: #007bff;
            color: white;
        }
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .stat-pendientes { border-top: 4px solid #ffc107; }
        .stat-aprobados { border-top: 4px solid #28a745; }
        .stat-rechazados { border-top: 4px solid #dc3545; }
        .stat-totales { border-top: 4px solid #007bff; }
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
        .estado-pendiente { color: #ffc107; font-weight: bold; }
        .estado-aprobado { color: #28a745; font-weight: bold; }
        .estado-rechazado { color: #dc3545; font-weight: bold; }
        .estado-entregado { color: #17a2b8; font-weight: bold; }
        .btn {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            font-size: 0.85em;
        }
        .btn-primary {
            background: #007bff;
            color: white;
        }
        .btn-success {
            background: #28a745;
            color: white;
        }
        .btn-danger {
            background: #dc3545;
            color: white;
        }
        .alert {
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 15px;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
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
                            <span class="text nav-text">Mis Tickets</span>
                        </a>
                    </li>
                    <?php if (isset($_SESSION['rol']) && $_SESSION['rol'] == 1): ?>
                    <li class="nav-link">
                        <a href="./admin.php">
                            <i class='bx bx-cog icon'></i>
                            <span class="text nav-text">Panel Admin</span>
                        </a>
                    </li>
                    <li class="nav-link">
                        <a href="./admin_tickets.php">
                            <i class='bx bx-hourglass icon'></i>
                            <span class="text nav-text">Solicitudes</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="bottom-content">
                <li class="">
                    <a href="logout.php">
                        <i class='bx bx-log-out icon' ></i>
                        <span class="text nav-text">Cerrar Sesión</span>
                    </a>
                </li>
                <li class="mode">
                    <div class="sun-moon">
                        <i class='bx bx-moon icon moon'></i>
                        <i class='bx bx-sun icon sun'></i>
                    </div>
                    <span class="mode-text text">Tema</span>
                    <div class="toggle-switch">
                        <span class="switch"></span>
                    </div>
                </li>
            </div>
        </div>
    </nav>

    <section class="home">
        <section class="banner">
            <h1 class="texto" style="text-shadow: #00000071 1px 0 10px;">Solicitudes de Usuarios</h1>
        </section>

        <div class="admin-container">
            <?php if (isset($_SESSION['ticket_success'])): ?>
                <div class="alert alert-success">
                    <?php echo $_SESSION['ticket_success']; unset($_SESSION['ticket_success']); ?>
                </div>
            <?php endif; ?>
            
            <?php if (isset($_SESSION['ticket_error'])): ?>
                <div class="alert alert-error">
                    <?php echo $_SESSION['ticket_error']; unset($_SESSION['ticket_error']); ?>
                </div>
            <?php endif; ?>

            <!-- Estadísticas -->
            <div class="stats-cards">
                <div class="stat-card stat-pendientes">
                    <div class="stat-number"><?php echo $pendientes; ?></div>
                    <div>Pendientes</div>
                </div>
                <div class="stat-card stat-aprobados">
                    <div class="stat-number"><?php echo $aprobados; ?></div>
                    <div>Aprobados</div>
                </div>
                <div class="stat-card stat-rechazados">
                    <div class="stat-number"><?php echo $rechazados; ?></div>
                    <div>Rechazados</div>
                </div>
                <div class="stat-card stat-totales">
                    <div class="stat-number"><?php echo $totales; ?></div>
                    <div>Total Solicitudes</div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="filtros">
                <a href="?estado=pendiente" class="filtro-btn <?php echo $estado == 'pendiente' ? 'active' : ''; ?>">
                    ⏳ Pendientes (<?php echo $pendientes; ?>)
                </a>
                <a href="?estado=aprobado" class="filtro-btn <?php echo $estado == 'aprobado' ? 'active' : ''; ?>">
                    ✅ Aprobados (<?php echo $aprobados; ?>)
                </a>
                <a href="?estado=rechazado" class="filtro-btn <?php echo $estado == 'rechazado' ? 'active' : ''; ?>">
                    ❌ Rechazados (<?php echo $rechazados; ?>)
                </a>
                <a href="?estado=entregado" class="filtro-btn <?php echo $estado == 'entregado' ? 'active' : ''; ?>">
                    📦 Entregados
                </a>
                <a href="?estado=todos" class="filtro-btn <?php echo $estado == 'todos' ? 'active' : ''; ?>">
                    📋 Todos (<?php echo $totales; ?>)
                </a>
            </div>

            <!-- Tabla de tickets -->
            <div class="card">
                <h2>Solicitudes de Usuarios <?php echo $estado != 'todos' ? ucfirst($estado) : ''; ?></h2>
                
                <?php if (count($tickets) > 0): ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>N° Ticket</th>
                            <th>Solicitante</th>
                            <th>Tipo</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($ticket['numero_ticket']); ?></strong></td>
                            <td><?php echo htmlspecialchars($ticket['usuario_nombre']); ?></td>
                            <td>
                                <?php 
                                $iconos = [
                                    'Herramientas' => '🛠️',
                                    'Materiales' => '📦', 
                                    'Medición' => '📏'
                                ];
                                echo ($iconos[$ticket['tipo_solicitud']] ?? '') . ' ' . htmlspecialchars($ticket['tipo_solicitud']);
                                ?>
                            </td>
                            <td class="estado-<?php echo $ticket['estado']; ?>">
                                <?php echo ucfirst($ticket['estado']); ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_solicitud'])); ?></td>
                            <td>
                                <a href="detalle_ticket.php?id=<?php echo $ticket['idTicket']; ?>" class="btn btn-primary">
                                    👁️ Ver
                                </a>
                                <?php if ($ticket['estado'] == 'pendiente'): ?>
                                <a href="aprobar_ticket.php?id=<?php echo $ticket['idTicket']; ?>" class="btn btn-success" onclick="return confirm('¿Aprobar esta solicitud? Se descontará del stock.')">
                                    ✅ Aprobar
                                </a>
                                <a href="rechazar_ticket.php?id=<?php echo $ticket['idTicket']; ?>" class="btn btn-danger" onclick="return confirm('¿Rechazar esta solicitud?')">
                                    ❌ Rechazar
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No hay solicitudes de usuarios <?php echo $estado == 'todos' ? '' : $estado; ?> para mostrar.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script src="./scripts/script.js"></script>
</body>
</html>