<?php
session_start();
require 'sifer_db.php';

if (!isset($_SESSION['nombre'])) {
    header('Location: login.php');
    exit();
}


$usuario_id = $_SESSION['id'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    <title>Sistema de Tickets - SIFER</title>
   <style>
    .tickets-container {
        padding: 20px;
        max-width: 1200px;
        margin: 0 auto;
    }
    .card {
        background: white;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        height: fit-content; /* ¡ESTA ES LA CLAVE! */
    }
    .form-group {
        margin-bottom: 15px;
    }
    label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    input, select, textarea {
        width: 100%;
        padding: 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    .btn {
        padding: 10px 20px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        margin: 5px;
    }
    .btn-primary {
        background: #007bff;
        color: white;
    }
    .btn-success {
        background: #28a745;
        color: white;
    }
    .btn-warning {
        background: #ffc107;
        color: black;
    }
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
    .item-row {
        border: 1px solid #ddd;
        padding: 15px;
        margin-bottom: 10px;
        border-radius: 4px;
        background-color: #f8f9fa;
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
                        <i class='bx bx-user-hexagon icon' ></i>
                        <span class="text nav-text">Cerrar Sesión</span>
                    </a>
                </li>

            </div>
        </div>
    </nav>

    <section class="home">
        <section class="banner">
            <h1 class="texto" style="text-shadow: #00000071 1px 0 10px;">Sistema de Tickets</h1>
        </section>

        <div class="tickets-container">
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

            <!-- Formulario para nuevo ticket -->
           <div class="card">
    <h2>📋 Nueva Solicitud de Taller</h2>
    <form id="formTicket" method="POST" action="procesar_ticket.php" class="compact-form">
        <div class="form-group">
            <label for="tipo_solicitud">Tipo de Material:</label>
            <select id="tipo_solicitud" name="tipo_solicitud" required>
                <option value="">Seleccionar...</option>
                <option value="Herramientas">🛠️ Herramientas</option>
                <option value="Materiales">📦 Materiales</option>
                <option value="Medición">📏 Medición</option>
            </select>
        </div>
        
        <div class="form-group">
            <label for="observaciones">Observaciones (opcional):</label>
            <textarea id="observaciones" name="observaciones" rows="2" placeholder="Ej: Para proyecto específico, urgencia, etc..."></textarea>
        </div>
        
        <h3>🛠️ Materiales Solicitados</h3>
        <div id="items-container" class="items-container-limited">
            <div class="item-row">
                <div class="form-group">
                    <label>Producto:</label>
                    <select class="producto-select" name="productos[0][id]" required style="font-size: 0.85em;">
                        <option value="">Seleccionar producto...</option>
                        <?php
                        $productos = $pdo->query("SELECT idProducto, nombreProducto, cantidad FROM productos WHERE cantidad > 0 ORDER BY nombreProducto");
                        while ($producto = $productos->fetch()):
                        ?>
                        <option value="<?php echo $producto['idProducto']; ?>">
                            <?php echo htmlspecialchars($producto['nombreProducto']); ?> (Stock: <?php echo $producto['cantidad']; ?>)
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Cantidad:</label>
                    <input type="number" class="cantidad" name="productos[0][cantidad]" min="1" required style="padding: 4px 8px;">
                </div>
                <button type="button" class="btn btn-warning remover-item">🗑️ Remover</button>
            </div>
        </div>
        
        <div style="margin-top: 15px;">
            <button type="button" class="btn btn-primary" id="agregar-item">➕ Agregar Material</button>
            <button type="submit" class="btn btn-success">✅ Crear Solicitud</button>
        </div>
    </form>
</div>

            <!-- Lista de tickets existentes -->
            <div class="card">
                <h2>📄 Mis Tickets</h2>
                <?php
                $sql = "SELECT t.*, u.nombre as usuario_nombre 
                        FROM tickets t 
                        JOIN usuarios u ON t.usuario_solicitante = u.id 
                        WHERE t.usuario_solicitante = ? 
                        ORDER BY t.fecha_solicitud DESC";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuario_id]);
                $tickets = $stmt->fetchAll();

                if (count($tickets) > 0):
                ?>
                <table class="table">
                    <thead>
                        <tr>
                            <th>N° Ticket</th>
                            <th>Tipo de solicitud</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tickets as $ticket): ?>
                        <tr>
                            <td><strong><?php echo htmlspecialchars($ticket['numero_ticket']); ?></strong></td>
                            <td><?php echo htmlspecialchars($ticket['tipo_solicitud']); ?></td>
                            <td class="estado-<?php echo $ticket['estado']; ?>">
                                <?php echo ucfirst($ticket['estado']); ?>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($ticket['fecha_solicitud'])); ?></td>
                            <td>
                                <button class="btn btn-primary" onclick="verDetalle(<?php echo $ticket['idTicket']; ?>)">
                                    👁️ Ver Detalle
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php else: ?>
                    <p>No tenés tickets creados todavía.</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let itemCount = 1;

            document.getElementById('agregar-item').addEventListener('click', function() {
                const container = document.getElementById('items-container');
                const newItem = document.createElement('div');
                newItem.className = 'item-row';
                newItem.innerHTML = `
                    <div class="form-group">
                        <label>Producto:</label>
                        <select class="producto-select" name="productos[${itemCount}][id]" required>
                            <option value="">Seleccionar producto...</option>
                            <?php
                            $productos = $pdo->query("SELECT idProducto, nombreProducto, cantidad FROM productos WHERE cantidad > 0 ORDER BY nombreProducto");
                            while ($producto = $productos->fetch()):
                            ?>
                            <option value="<?php echo $producto['idProducto']; ?>">
                                <?php echo htmlspecialchars($producto['nombreProducto']); ?> (Stock: <?php echo $producto['cantidad']; ?>)
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cantidad Solicitada:</label>
                        <input type="number" class="cantidad" name="productos[${itemCount}][cantidad]" min="1" required>
                    </div>
                    <button type="button" class="btn btn-warning remover-item">🗑️ Remover</button>
                `;
                container.appendChild(newItem);
                itemCount++;


                newItem.querySelector('.remover-item').addEventListener('click', function() {
                    if (document.querySelectorAll('.item-row').length > 1) {
                        newItem.remove();
                    }
                });
            });


            document.querySelectorAll('.remover-item').forEach(button => {
                button.addEventListener('click', function() {
                    if (document.querySelectorAll('.item-row').length > 1) {
                        this.closest('.item-row').remove();
                    }
                });
            });
        });

        function verDetalle(ticketId) {
            window.location.href = 'detalle_ticket.php?id=' + ticketId;
        }
    </script>
    <script src="./scripts/script.js"></script>
</body>
</html>