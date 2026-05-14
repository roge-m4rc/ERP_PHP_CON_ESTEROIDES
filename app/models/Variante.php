<?php
if (class_exists('Variante')) return;

class Variante {
    private $conn;
    private $table = "variantes";

    public $producto_id;
    public $talla;
    public $color;
    public $stock;

    public function __construct($db) {
        $this->conn = $db;
    }

    // Devolver tallas únicas para compatibilidad con vistas
    public function getTallas() {
        $stmt = $this->conn->prepare("SELECT DISTINCT talla as nombre FROM variantes WHERE talla IS NOT NULL AND talla != '' ORDER BY talla ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getColores() {
        $stmt = $this->conn->prepare("SELECT DISTINCT color as nombre FROM variantes WHERE color IS NOT NULL AND color != '' ORDER BY color ASC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function listarPorProducto($prod_id) {
        $query = "SELECT v.*, v.talla as nombre_talla, v.color as nombre_color 
                  FROM " . $this->table . " v
                  WHERE v.producto_id = :pid ORDER BY v.id ASC";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pid', $prod_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // CORRECCIÓN: Quitada 'codigo' del INSERT
    public function crear() {
        $query = "INSERT INTO " . $this->table . " 
                  (producto_id, talla, color, stock) 
                  VALUES (:pid, :talla, :color, :stock)";
        
        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(':pid', $this->producto_id);
        $stmt->bindParam(':talla', $this->talla);
        $stmt->bindParam(':color', $this->color);
        $stmt->bindParam(':stock', $this->stock);

        try {
            if($stmt->execute()) return true;
        } catch (PDOException $e) {
            return false;
        }
    }

    public function actualizarStock($id_variante, $nuevo_stock) {
        $query = "UPDATE " . $this->table . " SET stock = :stk WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':stk', $nuevo_stock);
        $stmt->bindParam(':id', $id_variante);
        return $stmt->execute();
    }

    public function eliminar($id_variante) {
        try {
            $query = "DELETE FROM " . $this->table . " WHERE id = :id";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':id', $id_variante);
            return $stmt->execute();
        } catch (PDOException $e) {
            return false;
        }
    }
        public function getStockTotalPorProducto($producto_id) {
        $query = "SELECT COALESCE(SUM(stock), 0) as total FROM " . $this->table . " WHERE producto_id = :pid";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':pid', $producto_id);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }
    
}
?>