<?php
if (class_exists('Producto')) return;

class Producto {
    private $conn;
    private $table = "productos";

    public $id;
    public $codigo;
    public $nombre;
    public $categoria_id;
    public $precio_compra;
    public $precio_venta;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function listar() {
        $query = "SELECT p.*, c.nombre as nombre_categoria 
                  FROM " . $this->table . " p
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  WHERE p.estado = 1
                  ORDER BY p.id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getById($id) {
        $query = "SELECT p.*, c.nombre as nombre_categoria 
                  FROM " . $this->table . " p
                  LEFT JOIN categorias c ON p.categoria_id = c.id
                  WHERE p.id = :id LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

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
    // CORRECCIÓN: Generar código automático si está vacío
    public function crear() {
        if (empty($this->codigo)) {
            $this->codigo = $this->generarCodigoUnico();
        }

        // ✅ Validar nombre duplicado
        if ($this->existeNombre($this->nombre)) {
            return false;
        }

        $query = "INSERT INTO " . $this->table . " 
                  (codigo, nombre, categoria_id, precio_compra, precio_venta, stock_total) 
                  VALUES (:codigo, :nombre, :cat, :p_compra, :p_venta, 0)";
        
        $stmt = $this->conn->prepare($query);
        $this->nombre = htmlspecialchars(strip_tags($this->nombre));
        
        $stmt->bindParam(':codigo', $this->codigo);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':cat', $this->categoria_id);
        $stmt->bindParam(':p_compra', $this->precio_compra);
        $stmt->bindParam(':p_venta', $this->precio_venta);

        if($stmt->execute()) {
            return $this->conn->lastInsertId();
        }
        return false;
    }

    // NUEVO: Generar código único automático
    private function generarCodigoUnico() {
        $prefijo = 'PROD';
        $fecha = date('YmdHis');
        $random = rand(100, 999);
        $codigo = $prefijo . '-' . $fecha . '-' . $random;
        
        // Verificar que no exista
        $query = "SELECT COUNT(*) as total FROM " . $this->table . " WHERE codigo = :codigo";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo', $codigo);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Si existe (muy improbable), intentar de nuevo
        if ($row['total'] > 0) {
            return $this->generarCodigoUnico();
        }
        
        return $codigo;
    }

    public function actualizar($id) {
        // ✅ Validar nombre duplicado al editar
        if ($this->existeNombre($this->nombre, $id)) {
            return false;
        }

        if (empty($this->codigo)) {
            $this->codigo = $this->generarCodigoUnico();
        }
        
        $query = "UPDATE " . $this->table . " 
                  SET codigo = :codigo, nombre = :nombre, 
                      categoria_id = :cat, precio_compra = :p_compra, precio_venta = :p_venta
                  WHERE id = :id";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':codigo', $this->codigo);
        $stmt->bindParam(':nombre', $this->nombre);
        $stmt->bindParam(':cat', $this->categoria_id);
        $stmt->bindParam(':p_compra', $this->precio_compra);
        $stmt->bindParam(':p_venta', $this->precio_venta);
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