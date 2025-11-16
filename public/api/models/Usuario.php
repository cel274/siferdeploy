<?php
require_once 'Database.php';

class Usuario {
    private $db;
    private $table = 'usuarios';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register($nombre, $contraseña, $rol) {
        try {
            $this->db->beginTransaction();

            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE nombre = ?");
            $stmt->execute([$nombre]);
            
            if ($stmt->fetch()) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'El nombre de usuario ya existe'];
            }

            $stmt = $this->db->prepare("SELECT idRol FROM roles WHERE idRol = ?");
            $stmt->execute([$rol]);
            
            if (!$stmt->fetch()) {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'El rol especificado no existe'];
            }

            $password_hash = password_hash($contraseña, PASSWORD_DEFAULT);
            $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, contraseña, rol) VALUES (?, ?, ?)");
            
            if ($stmt->execute([$nombre, $password_hash, $rol])) {
                $user_id = $this->db->lastInsertId();
                $this->db->commit();
                return ['success' => true, 'user_id' => $user_id];
            } else {
                $this->db->rollBack();
                return ['success' => false, 'error' => 'Error al crear el usuario'];
            }

        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            error_log("Error en register: " . $e->getMessage());
            return ['success' => false, 'error' => 'Error en el registro: ' . $e->getMessage()];
        }
    }

    public function login($nombre, $contraseña) {
        try {
            $stmt = $this->db->prepare("SELECT u.*, r.nombreRol as rol_nombre FROM {$this->table} u 
                                      INNER JOIN roles r ON u.rol = r.idRol 
                                      WHERE u.nombre = ?");
            $stmt->execute([$nombre]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($contraseña, $user['contraseña'])) {
                unset($user['contraseña']);
                return $user;
            }
            return false;
        } catch (PDOException $e) {
            error_log("Error en login: " . $e->getMessage());
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
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuario: " . $e->getMessage());
            throw new Exception("Error al obtener usuario: " . $e->getMessage());
        }
    }

    public function getAll() {
        try {
            $stmt = $this->db->query("SELECT u.id, u.nombre, r.nombreRol as rol 
                                    FROM {$this->table} u 
                                    INNER JOIN roles r ON u.rol = r.idRol 
                                    ORDER BY u.id");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Error al obtener usuarios: " . $e->getMessage());
            throw new Exception("Error al obtener usuarios: " . $e->getMessage());
        }
    }
}
?>