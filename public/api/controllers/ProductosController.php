<?php
require_once __DIR__ . '/../models/Producto.php';

class ProductosController {
    private $productoModel;

    public function __construct() {
        $this->productoModel = new Producto();
    }

    public function getAll() {
        header('Content-Type: application/json');
        
        try {
            $productos = $this->productoModel->getAll();
            echo json_encode([
                'success' => true,
                'data' => $productos
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener productos: ' . $e->getMessage()
            ]);
        }
    }

    public function updateStock() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['idProducto']) || !isset($data['cantidad'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios']);
                return;
            }

            $success = $this->productoModel->updateStock($data['idProducto'], $data['cantidad']);

            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Stock actualizado correctamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Error al actualizar stock'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error del servidor: ' . $e->getMessage()
            ]);
        }
    }
}
?>