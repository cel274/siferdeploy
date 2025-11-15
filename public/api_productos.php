<?php
header('Content-Type: application/json');          
header('Access-Control-Allow-Origin: *');           
header('Access-Control-Allow-Methods: GET, POST');  
header('Access-Control-Allow-Headers: Content-Type'); 


require 'sifer_db.php';  

try {
  
    $sql = "SELECT idProducto, nombreProducto, cantidad, descripcion FROM productos";
    $resultado = $pdo->query($sql);  
    
  
    $productos = $resultado->fetchAll(PDO::FETCH_ASSOC);
   
    
 
    if ($productos) {
 
        echo json_encode([
            'success' => true,       
            'data' => $productos,    
            'count' => count($productos) 
        ]);
    } else {
     
        echo json_encode([
            'success' => true,
            'data' => [],         
            'count' => 0,
            'message' => 'No hay productos en el stock'
        ]);
    }
    
} catch (PDOException $e) {
  
    echo json_encode([
        'success' => false,
        'error' => 'Error de base de datos: ' . $e->getMessage()
    ]);
}

?>
