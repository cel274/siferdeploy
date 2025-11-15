<?php
require_once 'Database.php';

class Ticket {
    private $db;
    private $table = 'tickets';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create($usuario_solicitante, $tipo_solicitud, $items) {
        try {
            $this->db->beginTransaction();

            $numero_ticket = 'TKT' . date('Y') . str_pad($this->getNextTicketNumber(), 5, '0', STR_PAD_LEFT);

            $stmt = $this->db->prepare("INSERT INTO {$this->table} 
                                      (numero_ticket, usuario_solicitante, tipo_solicitud) 
                                      VALUES (?, ?, ?)");
            $stmt->execute([$numero_ticket, $usuario_solicitante, $tipo_solicitud]);
            $ticket_id = $this->db->lastInsertId();

            foreach ($items as $item) {
                $stmt = $this->db->prepare("INSERT INTO ticket_items 
                                          (ticket_id, producto_id, cantidad_solicitada) 
                                          VALUES (?, ?, ?)");
                $stmt->execute([$ticket_id, $item['producto_id'], $item['cantidad']]);
            }

            $this->db->commit();
            return $ticket_id;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new Exception("Error al crear ticket: " . $e->getMessage());
        }
    }

    private function getNextTicketNumber() {
        $stmt = $this->db->query("SELECT COUNT(*) as count FROM {$this->table} WHERE YEAR(fecha_solicitud) = YEAR(NOW())");
        $result = $stmt->fetch();
        return $result['count'] + 1;
    }

    public function getByUser($usuario_id) {
        try {
            $stmt = $this->db->prepare("SELECT t.*, u.nombre as solicitante_nombre 
                                      FROM {$this->table} t 
                                      INNER JOIN usuarios u ON t.usuario_solicitante = u.id 
                                      WHERE t.usuario_solicitante = ? 
                                      ORDER BY t.fecha_solicitud DESC");
            $stmt->execute([$usuario_id]);
            $tickets = $stmt->fetchAll();

            foreach ($tickets as &$ticket) {
                $ticket['items'] = $this->getTicketItems($ticket['idTicket']);
            }

            return $tickets;
        } catch (PDOException $e) {
            throw new Exception("Error al obtener tickets: " . $e->getMessage());
        }
    }

    public function getAll($filters = []) {
        try {
            $sql = "SELECT t.*, u.nombre as solicitante_nombre, 
                   a.nombre as aprobador_nombre 
                   FROM {$this->table} t 
                   INNER JOIN usuarios u ON t.usuario_solicitante = u.id 
                   LEFT JOIN usuarios a ON t.aprobado_por = a.id 
                   WHERE 1=1";

            $params = [];

            if (!empty($filters['estado'])) {
                $sql .= " AND t.estado = ?";
                $params[] = $filters['estado'];
            }

            if (!empty($filters['usuario_id'])) {
                $sql .= " AND t.usuario_solicitante = ?";
                $params[] = $filters['usuario_id'];
            }

            $sql .= " ORDER BY t.fecha_solicitud DESC";

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            $tickets = $stmt->fetchAll();

            foreach ($tickets as &$ticket) {
                $ticket['items'] = $this->getTicketItems($ticket['idTicket']);
            }

            return $tickets;
        } catch (PDOException $e) {
            throw new Exception("Error al obtener tickets: " . $e->getMessage());
        }
    }

    private function getTicketItems($ticket_id) {
        try {
            $stmt = $this->db->prepare("SELECT ti.*, p.nombreProducto, p.descripcion 
                                      FROM ticket_items ti 
                                      INNER JOIN productos p ON ti.producto_id = p.idProducto 
                                      WHERE ti.ticket_id = ?");
            $stmt->execute([$ticket_id]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener items: " . $e->getMessage());
        }
    }

    public function updateStatus($ticket_id, $estado, $aprobado_por = null, $observaciones = null) {
        try {
            $sql = "UPDATE {$this->table} SET estado = ?";
            $params = [$estado];

            if ($estado === 'aprobado') {
                $sql .= ", fecha_aprobacion = NOW(), aprobado_por = ?";
                $params[] = $aprobado_por;
            } elseif ($estado === 'entregado') {
                $sql .= ", fecha_entrega = NOW()";
            }

            if ($observaciones !== null) {
                $sql .= ", observaciones = ?";
                $params[] = $observaciones;
            }

            $sql .= " WHERE idTicket = ?";
            $params[] = $ticket_id;

            $stmt = $this->db->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar ticket: " . $e->getMessage());
        }
    }

    public function updateApprovedQuantities($ticket_id, $items) {
        try {
            $this->db->beginTransaction();

            foreach ($items as $item) {
                $stmt = $this->db->prepare("UPDATE ticket_items 
                                          SET cantidad_aprobada = ? 
                                          WHERE idItem = ? AND ticket_id = ?");
                $stmt->execute([$item['cantidad_aprobada'], $item['idItem'], $ticket_id]);
            }

            $this->db->commit();
            return true;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw new Exception("Error al actualizar cantidades: " . $e->getMessage());
        }
    }
}
?>