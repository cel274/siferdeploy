<?php
session_start(); 
require 'sifer_db.php';

if (!isset($_SESSION['nombre'])) {

}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="./css/style.css">
    <link href='https://cdn.boxicons.com/fonts/basic/boxicons.min.css' rel='stylesheet'>
    
    <title>Sifer</title> 
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
    <?php echo isset($_SESSION['nombre']) ? htmlspecialchars($_SESSION['nombre']) : 'Invitado'; ?>
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
                    <a href="login.php">
                        <i class='bx bx-user-hexagon icon' ></i>
                        <span class="text nav-text">Iniciar Sesión</span>
                    </a>
                </li>


                
            </div>
        </div>
    </nav>

    <section class="home">
        <section class="banner">
            <h1 class="texto" style="text-shadow: #00000071 1px 0 10px;">Stock de Productos</h1>
        </section>

        <div class="table-container">
            <?php
            $sql = "SELECT idProducto, nombreProducto, cantidad, descripcion FROM productos";
            $resultado = $pdo->query($sql);

            if ($resultado && $resultado->rowCount() > 0) {
                echo "
                <table class='stock-table'>
                    <thead>
                        <tr>
                            <th>ID Producto</th>
                            <th>Nombre Producto</th>
                            <th>Cantidad</th>
                            <th>Descripción</th>
                        </tr>
                    </thead>
                    <tbody>";
                
                while($row = $resultado->fetch(PDO::FETCH_ASSOC)) {
                    echo "
                        <tr>
                            <td>" . htmlspecialchars($row['idProducto']) . "</td>
                            <td>" . htmlspecialchars($row['nombreProducto']) . "</td>
                            <td>" . htmlspecialchars($row['cantidad']) . "</td>
                            <td>" . htmlspecialchars($row['descripcion']) . "</td>
                        </tr>";
                }
                
                echo "
                    </tbody>
                </table>";
            } else {
                echo "<p class='no-products'>No hay productos en el stock.</p>";
            }
            ?>
        </div>
    </section>

    <script src="./scripts/script.js"></script>

</body>
</html>
