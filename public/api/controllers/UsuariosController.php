<?php
require_once __DIR__ . '/../models/Usuario.php';

class UsuariosController {
    private $usuarioModel;

    public function __construct() {
        $this->usuarioModel = new Usuario();
    }

    public function getAll() {
        header('Content-Type: application/json');
        
        try {
            $usuarios = $this->usuarioModel->getAll();
            echo json_encode([
                'success' => true,
                'data' => $usuarios
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener usuarios: ' . $e->getMessage()
            ]);
        }
    }

    public function getById($id) {
        header('Content-Type: application/json');
        
        try {
            $usuario = $this->usuarioModel->getById($id);
            
            if ($usuario) {
                echo json_encode([
                    'success' => true,
                    'data' => $usuario
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'error' => 'Usuario no encontrado'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al obtener usuario: ' . $e->getMessage()
            ]);
        }
    }
}
?>