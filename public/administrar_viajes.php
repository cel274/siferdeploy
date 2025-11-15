<link rel="icon" href="imgs/logo.png" type="image/png">
<?php
session_start();
include("agvi_db.php");

if (!isset($_SESSION['nombre'])) {
    header("Location: index.php");
    exit();
}

$usuario = $_SESSION['nombre'];
$stmt = $pdo->prepare("SELECT rol FROM usuarios WHERE nombre = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$stmt->bind_result($rol);
$stmt->fetch();
$stmt->close();

if ($rol != 2) {
    header("Location: index.php");
    exit();
}

$mensaje = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['accionReserva'])) {
        if ($_POST['accionReserva'] === 'eliminar') {
            $idReserva = $_POST['idReserva'];
            $stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
            $stmt->bind_param("i", $idReserva);
            $stmt->execute();
            $stmt->close();
            $mensaje = "Reserva eliminada correctamente.";
        }
    } elseif (isset($_POST['accion'])) {
        if ($_POST['accion'] === 'eliminar') {
            $id = $_POST['id'];
            $stmt = $pdo->prepare("SELECT destino FROM viajes WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $resultado = $stmt->get_result();
            if ($resultado && $row = $resultado->fetch_assoc()) {
                $destino = $row['destino'];
                $nombreArchivo = strtolower(str_replace(' ', '', $destino)) . ".jpg";
                $rutaArchivo = __DIR__ . "/imgs/" . $nombreArchivo;
                if (file_exists($rutaArchivo)) {
                    unlink($rutaArchivo);
                }
            }
            $stmt->close();
            $stmt = $pdo->prepare("DELETE FROM viajes WHERE id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $stmt->close();
            $mensaje = "Viaje eliminado correctamente.";
        } elseif ($_POST['accion'] === 'editar') {
            $id = $_POST['id'];
            $destino = $_POST['destino'];
            $pais = $_POST['pais'];
            $precio = $_POST['precio'];
            $stmt = $pdo->prepare("UPDATE viajes SET destino = ?, pais = ?, precio = ? WHERE id = ?");
            $stmt->bind_param("ssdi", $destino, $pais, $precio, $id);
            $stmt->execute();
            $stmt->close();
            $mensaje = "Viaje actualizado correctamente.";
        } elseif ($_POST['accion'] === 'cambiar_imagen') {
            $id = $_POST['id'];
            $imagen = $_FILES['imagen'];
            if ($imagen && $imagen['error'] === 0) {
                $tipo = mime_content_type($imagen['tmp_name']);
                if ($tipo === 'image/jpeg') {
                    $stmt = $pdo->prepare("SELECT destino FROM viajes WHERE id = ?");
                    $stmt->bind_param("i", $id);
                    $stmt->execute();
                    $resultado = $stmt->get_result();
                    if ($resultado && $row = $resultado->fetch_assoc()) {
                        $destino = $row['destino'];
                        $nombreArchivo = strtolower(str_replace(' ', '', $destino)) . ".jpg";
                        $rutaDestino = __DIR__ . "/imgs/" . $nombreArchivo;
                        $origen = imagecreatefromjpeg($imagen['tmp_name']);
                        imagejpeg($origen, $rutaDestino, 90);
                        imagedestroy($origen);
                        $mensaje = "Imagen actualizada correctamente. (requiere borrar caché)";
                    } else {
                        $mensaje = "Viaje no encontrado.";
                    }
                    $stmt->close();
                } else {
                    $mensaje = "Solo se permite subir imágenes JPG.";
                }
            } else {
                $mensaje = "Error al subir la imagen.";
            }
        } else {
            $destino = $_POST['destino'] ?? '';
            $pais = $_POST['pais'] ?? '';
            $precio = $_POST['precio'] ?? '';
            $imagen = $_FILES['imagen'];
            if ($imagen && $imagen['error'] === 0) {
                $tipo = mime_content_type($imagen['tmp_name']);
                if ($tipo === 'image/jpeg') {
                    $nombreArchivo = strtolower(str_replace(' ', '', $destino)) . ".jpg";
                    $rutaDestino = __DIR__ . "/imgs/" . $nombreArchivo;
                    $origen = imagecreatefromjpeg($imagen['tmp_name']);
                    imagejpeg($origen, $rutaDestino, 90);
                    imagedestroy($origen);
                    $stmt = $pdo->prepare("INSERT INTO viajes (destino, pais, precio) VALUES (?, ?, ?)");
                    $stmt->bind_param("ssd", $destino, $pais, $precio);
                    if ($stmt->execute()) {
                        $mensaje = "Viaje agregado con éxito.";
                    } else {
                        $mensaje = "Error al agregar: " . $stmt->error;
                    }
                    $stmt->close();
                } else {
                    $mensaje = "Solo se permite subir imágenes JPG.";
                }
            } else {
                $mensaje = "Error al subir la imagen.";
            }
        }
    }
}

$viajes = $pdo->query("SELECT * FROM viajes ORDER BY id DESC");

$mensajeReservas = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accionReserva']) && $_POST['accionReserva'] === 'eliminar') {
    $idReserva = intval($_POST['idReserva']);
    $stmt = $pdo->prepare("DELETE FROM reservas WHERE id = ?");
    $stmt->bind_param("i", $idReserva);
    if ($stmt->execute()) {
        $mensajeReservas = "";
    } else {
        $mensajeReservas = "Error al eliminar la reserva: " . $stmt->error;
    }
    $stmt->close();
}

$sqlReservas = "
    SELECT r.id, u.nombre AS usuario, v.destino, v.pais, v.precio, r.cantidad, r.fechaReserva
    FROM reservas r
    INNER JOIN usuarios u ON r.idUsuario = u.id
    INNER JOIN viajes v ON r.idViaje = v.id
    ORDER BY r.fechaReserva DESC
";

$resultReservas = $pdo->query($sqlReservas);

$pdo->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8" />
<title>Administrar Viajes</title>
<style>
    body {
        background-color: #f1f5fc;
        font-family: Arial, sans-serif;
    }
    form {
        background-color: white;
        max-width: 500px;
        margin: 30px auto;
        padding: 30px;
        border-radius: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    h1 {
        text-align: center;
        color: #000;
    }
    label {
        font-weight: bold;
        display: block;
        margin-top: 15px;
    }
    input[type="text"],
    input[type="number"],
    select,
    input[type="file"] {
        width: 100%;
        padding: 10px;
        white-space: nowrap;
        margin-top: 5px;
        border: 1px solid #ccc;
        border-radius: 6px;
        box-sizing: border-box;
        font-size: 14px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    button {
        margin-top: 20px;
        padding: 12px;
        width: 100%;
        background-color: #261e7e;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 16px;
        cursor: pointer;
    }
    button:hover {
        background-color: rgb(30, 24, 99);
        transition: 0.3s;
    }
    .mensaje {
        text-align: center;
        margin: 20px;
        color: green;
        font-weight: bold;
    }
    table {
        width: 90%;
        margin: 40px auto;
        border-collapse: collapse;
        background: white;
        border-radius: 8px;
        overflow: hidden;
        font-size: 14px;
    }
    th, td {
        padding: 6px 8px;
        border: 1px solid #ccc;
        text-align: center;
        vertical-align: middle;
        line-height: 1.2;
        height: 36px;
    }
    td input[type="text"],
    td input[type="number"],
    td select {
        width: 90%;
        padding: 0px 8px;
        border: 1px solid #ccc;
        border-radius: 5px;
        box-sizing: border-box;
        font-size: 14px;
        height: 24px;
        white-space: nowrap;
        line-height: normal;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    form.editar-form {
        display: contents;
    }
    img.viaje-img {
        width: 80px;
        height: 60px;
        object-fit: cover;
        cursor: pointer;
        border-radius: 6px;
        border: 2px solid transparent;
        transition: border-color 0.3s;
    }
    img.viaje-img:hover {
        border-color: #261e7e;
    }
    input[type="file"].hidden-file {
        display: none;
    }
    .btn-accion {
        padding: 6px 12px;
        background-color: #261e7e;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        margin: 2px 4px;
        display: inline-block;
        height: 30px;
        line-height: 1.2;
    }
    .btn-accion:hover {
        background-color: rgb(30, 24, 99);
        transition: 0.3s;
    }
    .btn-eliminar {
        background-color: red;
    }
    .btn-eliminar:hover {
        background-color: darkred;
    }
    .btn-volver {
        background-color: #261e7e;
        color: white;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        cursor: pointer;
        padding: 10px 20px;
        margin-top: 10px;
    }
    .btn-volver:hover {
        background-color: rgb(30, 24, 99);
        transition: 0.3s;
    }
</style>

<script src="administrar.js"></script>
</head>
<body>

<h1>Administrar Viajes</h1>
<form method="POST" enctype="multipart/form-data">
    <label for="destino">Destino:</label>
    <input type="text" name="destino" required>
    <label for="pais">País (código):</label>
    <select name="pais" required>
        <option value="">Seleccionar país</option>
        <option value="AR">Argentina</option>
        <option value="BO">Bolivia</option>
        <option value="BR">Brasil</option>
        <option value="CL">Chile</option>
        <option value="CO">Colombia</option>
        <option value="CR">Costa Rica</option>
        <option value="CU">Cuba</option>
        <option value="DO">República Dominicana</option>
        <option value="EC">Ecuador</option>
        <option value="SV">El Salvador</option>
        <option value="GT">Guatemala</option>
        <option value="HN">Honduras</option>
        <option value="MX">México</option>
        <option value="NI">Nicaragua</option>
        <option value="PA">Panamá</option>
        <option value="PY">Paraguay</option>
        <option value="PE">Perú</option>
        <option value="PR">Puerto Rico</option>
        <option value="UY">Uruguay</option>
        <option value="VE">Venezuela</option>
    </select>
    <label for="precio">Precio (ARS):</label>
    <input type="number" name="precio" step="1000" required>
    <label for="imagen">Imagen (solo JPG):</label>
    <input type="file" name="imagen" accept=".jpg,.jpeg" required>
    <button type="submit">Agregar viaje</button>
</form>
<div style="text-align: center; margin-bottom: 20px;">
    <a href="index.php">
        <button class="btn-volver">Volver al Inicio</button>
    </a>
</div>
<?php if (!empty($mensaje)): ?>
    <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
<?php endif; ?>

<?php if ($viajes && $viajes->num_rows > 0): ?>
<table>
    <tr>
        <th>ID</th>
        <th>Imagen</th>
        <th>Destino</th>
        <th>País</th>
        <th>Precio</th>
        <th>Acciones</th>
    </tr>
    <?php while ($row = $viajes->fetch_assoc()):
        $nombreArchivo = strtolower(str_replace(' ', '', $row['destino'])) . ".jpg";
        $rutaImagen = "imgs/" . $nombreArchivo;
        $existeImagen = file_exists(__DIR__ . "/imgs/" . $nombreArchivo);
    ?>
    <form method="POST" enctype="multipart/form-data" class="editar-form" id="form-cambiar-img-<?php echo $row['id']; ?>">
        <input type="hidden" name="accion" value="cambiar_imagen">
        <input type="hidden" name="id" value="<?php echo $row['id']; ?>">
        <tr>
            <td><?php echo $row['id']; ?></td>
            <td>
                <input type="file" name="imagen" accept=".jpg,.jpeg" class="hidden-file" id="file-<?php echo $row['id']; ?>" onchange="enviarFormularioCambio(<?php echo $row['id']; ?>)">
                <img src="<?php echo $existeImagen ? $rutaImagen : 'imgs/default.jpg'; ?>" class="viaje-img" onclick="abrirSelectorImagen(<?php echo $row['id']; ?>)">
            </td>
            <td><input type="text" name="destino" value="<?php echo htmlspecialchars($row['destino']); ?>"></td>
            <td>
                <select name="pais">
                    <option value="AR" <?php if($row['pais']=='AR') echo 'selected'; ?>>Argentina</option>
                    <option value="BO" <?php if($row['pais']=='BO') echo 'selected'; ?>>Bolivia</option>
                    <option value="BR" <?php if($row['pais']=='BR') echo 'selected'; ?>>Brasil</option>
                    <option value="CL" <?php if($row['pais']=='CL') echo 'selected'; ?>>Chile</option>
                    <option value="CO" <?php if($row['pais']=='CO') echo 'selected'; ?>>Colombia</option>
                    <option value="CR" <?php if($row['pais']=='CR') echo 'selected'; ?>>Costa Rica</option>
                    <option value="CU" <?php if($row['pais']=='CU') echo 'selected'; ?>>Cuba</option>
                    <option value="DO" <?php if($row['pais']=='DO') echo 'selected'; ?>>República Dominicana</option>
                    <option value="EC" <?php if($row['pais']=='EC') echo 'selected'; ?>>Ecuador</option>
                    <option value="SV" <?php if($row['pais']=='SV') echo 'selected'; ?>>El Salvador</option>
                    <option value="GT" <?php if($row['pais']=='GT') echo 'selected'; ?>>Guatemala</option>
                    <option value="HN" <?php if($row['pais']=='HN') echo 'selected'; ?>>Honduras</option>
                    <option value="MX" <?php if($row['pais']=='MX') echo 'selected'; ?>>México</option>
                    <option value="NI" <?php if($row['pais']=='NI') echo 'selected'; ?>>Nicaragua</option>
                    <option value="PA" <?php if($row['pais']=='PA') echo 'selected'; ?>>Panamá</option>
                    <option value="PY" <?php if($row['pais']=='PY') echo 'selected'; ?>>Paraguay</option>
                    <option value="PE" <?php if($row['pais']=='PE') echo 'selected'; ?>>Perú</option>
                    <option value="PR" <?php if($row['pais']=='PR') echo 'selected'; ?>>Puerto Rico</option>
                    <option value="UY" <?php if($row['pais']=='UY') echo 'selected'; ?>>Uruguay</option>
                    <option value="VE" <?php if($row['pais']=='VE') echo 'selected'; ?>>Venezuela</option>
                </select>
            </td>
            <td><input type="number" name="precio" step="1000" value="<?php echo $row['precio']; ?>"></td>
            <td>
                <button type="submit" name="accion" value="editar" class="btn-accion">Guardar</button>
                <button type="submit" name="accion" value="eliminar" class="btn-accion btn-eliminar">Eliminar</button>
            </td>
        </tr>
    </form>
    <?php endwhile; ?>
</table>
<?php endif; ?>

<h1>Reservas</h1>

<?php if (!empty($mensajeReservas)): ?>
    <div class="mensaje"><?php echo htmlspecialchars($mensajeReservas); ?></div>
<?php endif; ?>

<?php if ($resultReservas && $resultReservas->num_rows > 0): ?>
<table>
    <tr>
        <th>ID Reserva</th>
        <th>Usuario</th>
        <th>Destino</th>
        <th>País</th>
        <th>Precio (ARS)</th>
        <th>Cantidad</th>
        <th>Fecha de Reserva</th>
        <th>Acciones</th>
    </tr>
    <?php while ($row = $resultReservas->fetch_assoc()): ?>
    <tr>
        <td><?php echo $row['id']; ?></td>
        <td><?php echo htmlspecialchars($row['usuario']); ?></td>
        <td><?php echo htmlspecialchars($row['destino']); ?></td>
        <td><?php echo htmlspecialchars($row['pais']); ?></td>
        <td><?php echo number_format($row['precio'], 2, ',', '.'); ?></td>
        <td><?php echo $row['cantidad']; ?></td>
        <td><?php echo $row['fechaReserva']; ?></td>
        <td>
            <form method="POST" style="margin:0; padding:0;">
                <input type="hidden" name="accionReserva" value="eliminar">
                <input type="hidden" name="idReserva" value="<?php echo $row['id']; ?>">
                <button type="submit" class="btn-accion btn-eliminar" style="padding:6px 12px; font-size:14px; height:30px; line-height:1.2; margin: 0;">Eliminar</button>
            </form>
        </td>
    </tr>
    <?php endwhile; ?>
</table>
<?php else: ?>
    <p style="text-align:center;">No hay reservas para mostrar.</p>
<?php endif; ?>

</body>
</html>

