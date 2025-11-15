<?php
require 'sifer_db.php';

echo "<h2>Verificación de Usuarios en la Base de Datos</h2>";

try {
    $stmt = $pdo->query("SELECT id, nombre, contraseña, rol, LENGTH(contraseña) as hash_length FROM usuarios");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<table border='1'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>Contraseña Hash</th><th>Longitud</th><th>Rol</th></tr>";
    
    foreach ($usuarios as $usuario) {
        echo "<tr>";
        echo "<td>" . $usuario['id'] . "</td>";
        echo "<td>" . $usuario['nombre'] . "</td>";
        echo "<td>" . $usuario['contraseña'] . "</td>";
        echo "<td>" . $usuario['hash_length'] . "</td>";
        echo "<td>" . $usuario['rol'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    

    echo "<h3>Probar contraseña 'admin' con cada usuario:</h3>";
    foreach ($usuarios as $usuario) {
        $resultado = password_verify('admin', $usuario['contraseña']);
        echo "<p>Usuario: " . $usuario['nombre'] . " - Contraseña 'admin' válida: " . ($resultado ? 'SÍ' : 'NO') . "</p>";
    }
    
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>