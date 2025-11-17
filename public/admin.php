<?php
session_start();
require 'sifer_db.php';
$tickets_pendientes = 0;
try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM tickets WHERE estado = 'pendiente'");
    $tickets_pendientes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
} catch (PDOException $e) {
    error_log("Error al obtener tickets pendientes: " . $e->getMessage());
}

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header('Location: index.php');
    exit();
}

try {
    $pdo = new PDO("mysql:host=shuttle.proxy.rlwy.net;port=14747;dbname=sifer;charset=utf8", "root", "TSuCpDkwTMYvSaaNxSMJHXKsifsdvZxv");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

$usuarios_totales = 0;
$productos_stock = 0;
$productos_bajos = 0;

try {
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM usuarios");
    $usuarios_totales = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos");
    $productos_stock = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM productos WHERE cantidad < 10");
    $productos_bajos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    
} catch (PDOException $e) {
    error_log("Error al obtener estadísticas: " . $e->getMessage());
}

$todos_usuarios = [];
try {
    $stmt = $pdo->query("SELECT u.id, u.nombre, r.nombreRol, r.idRol 
                         FROM usuarios u 
                         JOIN roles r ON u.rol = r.idRol 
                         ORDER BY u.id");
    $todos_usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error al obtener usuarios: " . $e->getMessage());
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
    
    <title>Administración - Sifer</title> 
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
        <h1 class="texto" style="text-shadow: #00000071 1px 0 10px;">Panel de Administración</h1>
    </section>

    <?php if (isset($_SESSION['admin_success'])): ?>
        <div class="alert alert-success">
            <?php echo $_SESSION['admin_success']; unset($_SESSION['admin_success']); ?>
        </div>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['admin_error'])): ?>
        <div class="alert alert-error">
            <?php echo $_SESSION['admin_error']; unset($_SESSION['admin_error']); ?>
        </div>
    <?php endif; ?>

    <section class="admin-content">
        <div class="cards">
<div class="card">
    <i class='bx bx-clipboard icon card-icon'></i>
    <div class="card-content">
        <div class="cont">
            <?php 
            $stmt = $pdo->query("SELECT COUNT(*) as total FROM tickets WHERE estado = 'pendiente'");
            echo $stmt->fetch()['total']; 
            ?>
        </div>
        <div class="cont2">Tickets Pendientes</div>
    </div>
</div>

            <div class="card">
                <i class='bx bx-user icon card-icon'></i>
                <div class="card-content">
                    <div class="cont"><?php echo $usuarios_totales; ?></div>
                    <div class="cont2">Usuarios Totales</div>
                </div>
            </div>

            <div class="card">
                <i class='bx bx-package icon card-icon'></i>
                <div class="card-content">
                    <div class="cont"><?php echo $productos_stock; ?></div>
                    <div class="cont2">Productos en Stock</div>
                </div>
            </div>

            <div class="card">
                <i class='bx bx-low-vision icon card-icon'></i>
                <div class="card-content">
                    <div class="cont"><?php echo $productos_bajos; ?></div>
                    <div class="cont2">Productos Bajos</div>
                </div>
            </div>
        </div>

            <div class="admin-section">
                <h2>Gestión de Productos</h2>
                <div class="admin-actions">
                    <button class="admin-btn" onclick="abrirModal('agregarUsuario')">
                        <i class='bx bx-plus-circle'></i>
                         Agregar Usuario
                    </button>
                     <button class="admin-btn" onclick="window.location.href='admin_tickets.php'">
                        <i class='bx bx-bar-chart-alt-2'></i>
                        Ver Solicitudes de Usuarios
                    </button>
</div>
    
                
                <div class="table-container" style="margin-top: 20px;">
                    <table class="admin-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($todos_usuarios as $usuario): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($usuario['id']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($usuario['nombreRol']); ?></td>
                                <td>
                                    <button class="admin-btn-small" onclick="editarUsuario(<?php echo $usuario['id']; ?>, '<?php echo htmlspecialchars($usuario['nombre']); ?>', <?php echo $usuario['idRol']; ?>)">
                                        <i class='bx bx-edit'></i>
                                    </button>
                                    <button class="admin-btn-small btn-danger" onclick="eliminarUsuario(<?php echo $usuario['id']; ?>)">
                                        <i class='bx bx-trash'></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="admin-section">
                <h2>Gestión de Productos</h2>
                <div class="admin-actions">
                    <button class="admin-btn" onclick="abrirModal('agregarProducto')">
                        <i class='bx bx-plus-circle'></i>
                        Agregar Producto
                    </button>
                    <button class="admin-btn" onclick="window.location.href='stock.php'">
                        <i class='bx bx-bar-chart-alt-2'></i>
                        Ver Stock Completo
                    </button>
                </div>
                <div class="table-container" style="margin-top: 20px;">
    <table class="admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Descripción</th>
                <th>Cantidad</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $productos = [];
            try {
                $stmt = $pdo->query("SELECT * FROM productos ORDER BY idProducto");
                $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log("Error al obtener productos: " . $e->getMessage());
            }
            
            foreach ($productos as $producto): 
            ?>
            <tr>
                <td><?php echo htmlspecialchars($producto['idProducto']); ?></td>
                <td><?php echo htmlspecialchars($producto['nombreProducto']); ?></td>
                <td><?php echo htmlspecialchars($producto['descripcion']); ?></td>
                <td><?php echo htmlspecialchars($producto['cantidad']); ?></td>
                <td>
                    <button class="admin-btn-small" onclick="editarProducto(<?php echo $producto['idProducto']; ?>, '<?php echo htmlspecialchars($producto['nombreProducto']); ?>', '<?php echo htmlspecialchars($producto['descripcion']); ?>', <?php echo $producto['cantidad']; ?>)">
                        <i class='bx bx-edit'></i>
                    </button>
                    <button class="admin-btn-small btn-danger" onclick="eliminarProducto(<?php echo $producto['idProducto']; ?>)">
                        <i class='bx bx-trash'></i>
                    </button>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
            </div>
        </section>
    </section>

    <div id="modalAgregarUsuario" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('agregarUsuario')">&times;</span>
            <h3>Agregar Nuevo Usuario</h3>
            <form id="formAgregarUsuario" method="POST" action="agregar_usuario.php">
                <div class="form-group">
                    <label for="nombreUsuario">Nombre de usuario:</label>
                    <input type="text" id="nombreUsuario" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="contraseña">Contraseña:</label>
                    <input type="password" id="contraseña" name="contraseña" required>
                </div>
                <div class="form-group">
                    <label for="rol">Rol:</label>
                    <select id="rol" name="rol" required>
                        <option value="1">Administrador</option>
                        <option value="2" selected>Usuario común</option>
                    </select>
                </div>
                <button type="submit" class="admin-btn">Agregar Usuario</button>
            </form>
        </div>
    </div>

    <div id="modalEditarUsuario" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('editarUsuario')">&times;</span>
            <h3>Editar Usuario</h3>
            <form id="formEditarUsuario" method="POST" action="editar_usuario.php">
                <input type="hidden" id="editar_id" name="id">
                <div class="form-group">
                    <label for="editar_nombre">Nombre de usuario:</label>
                    <input type="text" id="editar_nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="editar_rol">Rol:</label>
                    <select id="editar_rol" name="rol" required>
                        <option value="1">Administrador</option>
                        <option value="2">Usuario común</option>
                    </select>
                </div>
                <button type="submit" class="admin-btn">Actualizar Usuario</button>
            </form>
        </div>
    </div>

    <div id="modalConfirmarEliminar" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('confirmarEliminar')">&times;</span>
            <h3>Confirmar Eliminación</h3>
            <p>¿Estás seguro de que quieres eliminar este usuario?</p>
            <div class="modal-actions">
                <button class="admin-btn btn-danger" onclick="confirmarEliminacion()">Eliminar</button>
                <button class="admin-btn" onclick="cerrarModal('confirmarEliminar')">Cancelar</button>
            </div>
        </div>
    </div>


    <div id="modalAgregarProducto" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('agregarProducto')">&times;</span>
            <h3>Agregar Nuevo Producto</h3>
            <form id="formAgregarProducto" method="POST" action="agregar_producto.php">
                <div class="form-group">
                    <label for="nombreProducto">Nombre del producto:</label>
                    <input type="text" id="nombreProducto" name="nombreProducto" required>
                </div>
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <textarea id="descripcion" name="descripcion" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="cantidad">Cantidad:</label>
                    <input type="number" id="cantidad" name="cantidad" min="0" required>
                </div>
                <button type="submit" class="admin-btn">Agregar Producto</button>
            </form>
        </div>
    </div>


    <div id="modalEditarProducto" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('editarProducto')">&times;</span>
            <h3>Editar Producto</h3>
            <form id="formEditarProducto" method="POST" action="editar_producto.php">
                <input type="hidden" id="editar_idProducto" name="idProducto">
                <div class="form-group">
                    <label for="editar_nombreProducto">Nombre del producto:</label>
                    <input type="text" id="editar_nombreProducto" name="nombreProducto" required>
                </div>
                <div class="form-group">
                    <label for="editar_descripcion">Descripción:</label>
                    <textarea id="editar_descripcion" name="descripcion" rows="3" required></textarea>
                </div>
                <div class="form-group">
                    <label for="editar_cantidad">Cantidad:</label>
                    <input type="number" id="editar_cantidad" name="cantidad" min="0" required>
                </div>
                <button type="submit" class="admin-btn">Actualizar Producto</button>
            </form>
        </div>
    </div>


    <div id="modalConfirmarEliminarProducto" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="cerrarModal('confirmarEliminarProducto')">&times;</span>
            <h3>Confirmar Eliminación</h3>
            <p>¿Estás seguro de que quieres eliminar este producto?</p>
            <div class="modal-actions">
                <button class="admin-btn btn-danger" onclick="confirmarEliminacionProducto()">Eliminar</button>
                <button class="admin-btn" onclick="cerrarModal('confirmarEliminarProducto')">Cancelar</button>
            </div>
        </div>
    </div>

    <script src="./scripts/script.js"></script>
    <script>
        let usuarioAEliminar = null;

        function abrirModal(tipo) {
            document.getElementById('modal' + tipo.charAt(0).toUpperCase() + tipo.slice(1)).style.display = 'block';
        }

        function cerrarModal(tipo) {
            document.getElementById('modal' + tipo.charAt(0).toUpperCase() + tipo.slice(1)).style.display = 'none';
        }

        function mostrarUsuarios() {
            document.querySelector('.table-container').scrollIntoView({ behavior: 'smooth' });
        }

        function editarUsuario(id, nombre, rol) {
            document.getElementById('editar_id').value = id;
            document.getElementById('editar_nombre').value = nombre;
            document.getElementById('editar_rol').value = rol;
            abrirModal('editarUsuario');
        }

        function eliminarUsuario(id) {
            usuarioAEliminar = id;
            abrirModal('confirmarEliminar');
        }

        function confirmarEliminacion() {
            if (usuarioAEliminar) {
                window.location.href = 'eliminar_usuario.php?id=' + usuarioAEliminar;
            }
        }

        window.onclick = function(event) {
            const modals = document.getElementsByClassName('modal');
            for (let modal of modals) {
                if (event.target == modal) {
                    modal.style.display = 'none';
                }
            }
        }
        let productoAEliminar = null;

        function editarProducto(id, nombre, descripcion, cantidad) {
            document.getElementById('editar_idProducto').value = id;
            document.getElementById('editar_nombreProducto').value = nombre;
            document.getElementById('editar_descripcion').value = descripcion;
            document.getElementById('editar_cantidad').value = cantidad;
            abrirModal('editarProducto');
        }

        function eliminarProducto(id) {
            productoAEliminar = id;
            abrirModal('confirmarEliminarProducto');
        }

        function confirmarEliminacionProducto() {
            if (productoAEliminar) {
                window.location.href = 'eliminar_producto.php?id=' + productoAEliminar;
            }
        }
    </script>

</body>
</html>
