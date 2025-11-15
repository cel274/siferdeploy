<?php
require_once __DIR__ . '/Database.php';

class Producto {
    private $db;
    private $table = 'productos';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT * FROM {$this->table} ORDER BY idProducto");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener productos: " . $e->getMessage());
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM {$this->table} WHERE idProducto = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener producto: " . $e->getMessage());
        }
    }

    public function updateStock($idProducto, $nuevaCantidad) {
        try {
            $stmt = $this->db->prepare("UPDATE {$this->table} SET cantidad = ? WHERE idProducto = ?");
            return $stmt->execute([$nuevaCantidad, $idProducto]);
        } catch (PDOException $e) {
            throw new Exception("Error al actualizar stock: " . $e->getMessage());
        }
    }

    public function create($nombre, $descripcion, $cantidad) {
        try {
            $stmt = $this->db->prepare("INSERT INTO {$this->table} (nombreProducto, descripcion, cantidad) VALUES (?, ?, ?)");
            return $stmt->execute([$nombre, $descripcion, $cantidad]);
        } catch (PDOException $e) {
            throw new Exception("Error al crear producto: " . $e->getMessage());
        }
    }
}
?>