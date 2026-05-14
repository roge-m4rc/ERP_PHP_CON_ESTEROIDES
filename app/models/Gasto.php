<?php
if (class_exists('Gasto')) return;

class Gasto {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function crear($caja_id, $usuario_id, $monto, $descripcion) {
        if ($monto <= 0) return false;
        $sql  = "INSERT INTO gastos (caja_id, usuario_id, monto, descripcion, fecha) 
                 VALUES (:caja, :usr, :monto, :desc, datetime('now','localtime'))";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':caja',  $caja_id);
        $stmt->bindParam(':usr',   $usuario_id);
        $stmt->bindParam(':monto', $monto);
        $stmt->bindParam(':desc',  $descripcion);
        return $stmt->execute();
    }

    public function getTotalGastos($caja_id) {
        $sql  = "SELECT COALESCE(SUM(monto), 0) as total FROM gastos WHERE caja_id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $caja_id);
        $stmt->execute();
        return (float)$stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function listarPorCaja($caja_id) {
        $sql  = "SELECT * FROM gastos WHERE caja_id = :id ORDER BY fecha DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id', $caja_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar($id, $caja_id) {
        $sql  = "DELETE FROM gastos WHERE id = :id AND caja_id = :caja_id";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id',      $id);
        $stmt->bindParam(':caja_id', $caja_id);
        return $stmt->execute();
    }
}
?>
