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
            $this->db->begin_transaction();

            $stmt = $this->db->prepare("SELECT id FROM usuarios WHERE nombre = ?");
            $stmt->bind_param("s", $nombre);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                return ['success' => false, 'error' => 'El nombre de usuario ya existe'];
            }
            $stmt->close();

            $stmt = $this->db->prepare("SELECT idRol FROM roles WHERE idRol = ?");
            $stmt->bind_param("i", $rol);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 0) {
                return ['success' => false, 'error' => 'El rol especificado no existe'];
            }
            $stmt->close();

            $password_hash = password_hash($contraseña, PASSWORD_DEFAULT);

            $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, contraseña, rol) VALUES (?, ?, ?)");
            $stmt->bind_param("ssi", $nombre, $password_hash, $rol);
            
            if ($stmt->execute()) {
                $user_id = $this->db->insert_id;
                $this->db->commit();
                return ['success' => true, 'user_id' => $user_id];
            } else {
                $this->db->rollback();
                return ['success' => false, 'error' => 'Error al crear el usuario'];
            }

        } catch (Exception $e) {
            $this->db->rollback();
            throw new Exception("Error en registro: " . $e->getMessage());
        }
    }

    public function login($nombre, $contraseña) {
        try {
            $stmt = $this->db->prepare("SELECT u.*, r.nombreRol as rol_nombre FROM {$this->table} u 
                                      INNER JOIN roles r ON u.rol = r.idRol 
                                      WHERE u.nombre = ?");
            $stmt->execute([$nombre]);
            $user = $stmt->fetch();

            if ($user && password_verify($contraseña, $user['contraseña'])) {
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