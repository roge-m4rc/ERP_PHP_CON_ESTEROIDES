<?php
if (class_exists('Usuario')) return;

class Usuario {
    private $conn;
    private $table_name = "usuarios";

    public $id_usuario;
    public $nombre;
    public $usuario;
    public $password;
    public $rol;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($usuario, $password) {
    // 1. Limpiamos el input
        $usuario_clean = strtolower(trim($usuario));
        
        // 2. Consulta directa para no fallar
        $query = "SELECT id_usuario, nombre, password, rol FROM usuarios WHERE usuario = :usuario AND estado = 1 LIMIT 1";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usuario', $usuario_clean);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        // --- BLOQUE DE DIAGNÓSTICO (Borrar después de entrar) ---
        if (!$row) {
            // Si entra aquí, el usuario NO existe en la BD nueva
            die("Error: El usuario '" . $usuario_clean . "' no existe en la base de datos.");
        }
        
        if (!password_verify($password, $row['password'])) {
            // Si entra aquí, la clave no coincide con el hash
            die("Error: La clave es incorrecta. Hash en BD: " . $row['password']);
        }
        // --- FIN BLOQUE DE DIAGNÓSTICO ---

        // Si pasó los die(), entonces todo está bien
        $this->id_usuario = $row['id_usuario'];
        $this->nombre = $row['nombre'];
        $this->rol = $row['rol'];
        return true;
    }

    public function listar() {
        $query = "SELECT id_usuario, nombre, usuario, rol, estado, fecha_creacion FROM " . $this->table_name . " ORDER BY id_usuario ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table_name . " WHERE id_usuario = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($nombre, $usuario, $password_plana, $rol) {
        $password_hash = password_hash($password_plana, PASSWORD_DEFAULT);

        $query = "INSERT INTO " . $this->table_name . " (nombre, usuario, password, rol) VALUES (:nom, :usr, :pass, :rol)";
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':nom', $nombre);
        $stmt->bindParam(':usr', $usuario);
        $stmt->bindParam(':pass', $password_hash);
        $stmt->bindParam(':rol', $rol);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function actualizar($id, $nombre, $usuario, $rol, $estado, $password = null) {
        if ($password) {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $query = "UPDATE " . $this->table_name . " 
                      SET nombre = :nom, usuario = :usr, rol = :rol, estado = :est, password = :pass 
                      WHERE id_usuario = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':pass', $password_hash);
        } else {
            $query = "UPDATE " . $this->table_name . " 
                      SET nombre = :nom, usuario = :usr, rol = :rol, estado = :est 
                      WHERE id_usuario = :id";
            $stmt = $this->conn->prepare($query);
        }

        $stmt->bindParam(':nom', $nombre);
        $stmt->bindParam(':usr', $usuario);
        $stmt->bindParam(':rol', $rol);
        $stmt->bindParam(':est', $estado);
        $stmt->bindParam(':id', $id);

        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id_usuario = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
    
    public function existeUsuario($usuario) {
        $query = "SELECT id_usuario FROM " . $this->table_name . " WHERE usuario = :usr";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':usr', $usuario);
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }
}
?>