<?php
if (class_exists('Categoria')) return;

class Categoria {
    private $conn;
    private $table = "categorias";

    public $id;
    public $nombre;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $query = "SELECT * FROM " . $this->table . " WHERE estado = 1 ORDER BY id ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT * FROM " . $this->table . " WHERE id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // ✅ NUEVO: Verificar si ya existe (insensible a mayúsculas/minúsculas)
    public function existeNombre($nombre, $excluir_id = null) {
        $nombre = strtolower(trim($nombre));
        $query = "SELECT id FROM " . $this->table . " WHERE LOWER(TRIM(nombre)) = :nombre AND estado = 1";
        
        if ($excluir_id) {
            $query .= " AND id != :excluir";
        }
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':nombre', $nombre);
        if ($excluir_id) {
            $stmt->bindParam(':excluir', $excluir_id);
        }
        $stmt->execute();
        return $stmt->rowCount() > 0;
    }

    public function crear() {
        // ✅ Validar duplicado
        if ($this->existeNombre($this->nombre)) {
            return false; // Ya existe
        }

        $query = "INSERT INTO " . $this->table . " (nombre) VALUES (:nombre)";
        $stmt = $this->conn->prepare($query);
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        $stmt->bindParam(':nombre', $this->nombre);
        return $stmt->execute();
    }

    public function actualizar($id, $nombre) {
        // ✅ Validar duplicado al editar (excluyendo el propio ID)
        if ($this->existeNombre($nombre, $id)) {
            return false;
        }

        $query = "UPDATE " . $this->table . " SET nombre = :nombre WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $nombre = htmlspecialchars(strip_tags($nombre));
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    public function eliminar($id) {
        $query = "UPDATE " . $this->table . " SET estado = 0 WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}
?>