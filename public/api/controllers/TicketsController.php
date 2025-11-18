<?php
require_once __DIR__ . '/../models/Ticket.php';
require_once __DIR__ . '/../models/Producto.php';

class TicketsController {
    private $ticketModel;
    private $productoModel;

    public function __construct() {
        $this->ticketModel = new Ticket();
        $this->productoModel = new Producto();
    }

    public function create() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['usuario_solicitante']) || !isset($data['items'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios']);
                return;
            }

            $tipo_solicitud = $data['tipo_solicitud'] ?? 'Herramientas';
            $ticket_id = $this->ticketModel->create($data['usuario_solicitante'], $tipo_solicitud, $data['items']);

            echo json_encode([
                'success' => true,
                'ticket_id' => $ticket_id,
                'message' => 'Ticket creado correctamente'
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'error' => 'Error al crear ticket: ' . $e->getMessage()
            ]);
        }
    }

    public function getByUser($user_id) {
    header('Content-Type: application/json');
    
    try {
        $tickets = $this->ticketModel->getByUser($user_id);
        echo json_encode([
            'success' => true,
            'user' => $tickets
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener tickets: ' . $e->getMessage()
        ]);
    }
}
    public function updateApprovedQuantities() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['ticket_id']) || !isset($data['items'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios']);
                return;
            }

            $success = $this->ticketModel->updateApprovedQuantities($data['ticket_id'], $data['items']);

            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Cantidades aprobadas actualizadas correctamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Error al actualizar cantidades aprobadas'
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
  public function getAll() {
    header('Content-Type: application/json');
    
    try {
        $filters = [];
        if (isset($_GET['estado'])) {
            $filters['estado'] = $_GET['estado'];
        }
        if (isset($_GET['usuario_id'])) {
            $filters['usuario_id'] = $_GET['usuario_id'];
        }

        $tickets = $this->ticketModel->getAll($filters);
        echo json_encode([
            'success' => true,
            'user' => $tickets
        ]);
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener tickets: ' . $e->getMessage()
        ]);
    }
}

    public function updateStatus() {
        header('Content-Type: application/json');
        
        try {
            $data = json_decode(file_get_contents('php://input'), true);
            
            if (!isset($data['ticket_id']) || !isset($data['estado'])) {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Faltan campos obligatorios']);
                return;
            }

            $aprobado_por = $data['aprobado_por'] ?? null;
            $observaciones = $data['observaciones'] ?? null;

            $success = $this->ticketModel->updateStatus($data['ticket_id'], $data['estado'], $aprobado_por, $observaciones);

            if ($success) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Estado del ticket actualizado correctamente'
                ]);
            } else {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'error' => 'Error al actualizar estado del ticket'
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
