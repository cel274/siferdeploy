<?php
require_once 'Database.php';

class Usuario {
    private $db;
    private $table = 'usuarios';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function login($nombre, $contraseña) {
        try {
            $stmt = $this->db->prepare("SELECT u.*, r.nombreRol as rol_nombre FROM {$this->table} u 
                                      INNER JOIN roles r ON u.rol = r.idRol 
                                      WHERE u.nombre = ?");
            $stmt->execute([$nombre]);
            $user = $stmt->fetch();

            if ($user && password_verify($contraseña, $user['contraseña'])) {
                // Eliminar la contraseña del resultado
                unset($user['contraseña']);
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            throw new Exception("Error en login: " . $e->getMessage());
        }
    }

    public function getById($id) {
        try {
            $stmt = $this->db->prepare("SELECT u.id, u.nombre, r.nombreRol as rol, r.orden 
                                      FROM {$this->table} u 
                                      INNER JOIN roles r ON u.rol = r.idRol 
                                      WHERE u.id = ?");
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener usuario: " . $e->getMessage());
        }
    }

    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT u.id, u.nombre, r.nombreRol as rol 
                                    FROM {$this->table} u 
                                    INNER JOIN roles r ON u.rol = r.idRol 
                                    ORDER BY u.id");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error al obtener usuarios: " . $e->getMessage());
        }
    }
}
?>