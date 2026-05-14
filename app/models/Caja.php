<?php
if (class_exists('Caja')) return;

class Caja {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function obtenerCajaAbierta($usuario_id) {
        $query = "SELECT * FROM cajas 
                  WHERE usuario_id = :uid AND estado = 1 
                  LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $usuario_id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function abrir($usuario_id, $monto_inicial) {
        $existente = $this->obtenerCajaAbierta($usuario_id);
        if ($existente) return false;

        $query = "INSERT INTO cajas (usuario_id, monto_inicial, estado, fecha_apertura) 
                  VALUES (:uid, :monto, 1, datetime('now', 'localtime'))";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':uid', $usuario_id);
        $stmt->bindParam(':monto', $monto_inicial);
        return $stmt->execute();
    }

    public function calcularTotales($id_caja) {
        $sqlTotal = "SELECT COALESCE(SUM(total), 0) as total FROM ventas
                     WHERE caja_id = :caja_id AND estado = 1";
        $stmt = $this->conn->prepare($sqlTotal);
        $stmt->bindParam(':caja_id', $id_caja);
        $stmt->execute();
        $total_global = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $sqlEfectivo = "SELECT COALESCE(SUM(total), 0) as total FROM ventas
                        WHERE caja_id = :caja_id AND estado = 1 AND metodo_pago = 'EFECTIVO'";
        $stmt = $this->conn->prepare($sqlEfectivo);
        $stmt->bindParam(':caja_id', $id_caja);
        $stmt->execute();
        $total_efectivo = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $sqlYape = "SELECT COALESCE(SUM(total), 0) as total FROM ventas
                    WHERE caja_id = :caja_id AND estado = 1 AND metodo_pago = 'YAPE'";
        $stmt = $this->conn->prepare($sqlYape);
        $stmt->bindParam(':caja_id', $id_caja);
        $stmt->execute();
        $total_yape = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $sqlTarjeta = "SELECT COALESCE(SUM(total), 0) as total FROM ventas
                       WHERE caja_id = :caja_id AND estado = 1 AND metodo_pago = 'TARJETA'";
        $stmt = $this->conn->prepare($sqlTarjeta);
        $stmt->bindParam(':caja_id', $id_caja);
        $stmt->execute();
        $total_tarjeta = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $sqlGastos = "SELECT COALESCE(SUM(monto), 0) as total FROM gastos WHERE caja_id = :id";
        $stmt = $this->conn->prepare($sqlGastos);
        $stmt->bindParam(':id', $id_caja);
        $stmt->execute();
        $total_gastos = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        $sqlCantidad = "SELECT COUNT(*) as total FROM ventas WHERE caja_id = :caja_id AND estado = 1";
        $stmt = $this->conn->prepare($sqlCantidad);
        $stmt->bindParam(':caja_id', $id_caja);
        $stmt->execute();
        $cantidad_tickets = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

        return [
            'venta_total'      => (float)$total_global,
            'venta_efectivo'   => (float)$total_efectivo,
            'venta_yape'       => (float)$total_yape,
            'venta_tarjeta'    => (float)$total_tarjeta,
            'venta_digital'    => (float)($total_yape + $total_tarjeta),
            'gastos'           => (float)$total_gastos,
            'cantidad_tickets' => (int)$cantidad_tickets
        ];
    }

    public function cerrar($id_caja, $total_ventas, $monto_final) {
        $query = "UPDATE cajas 
                  SET fecha_cierre = datetime('now', 'localtime'), 
                      monto_final  = :final,
                      total_ventas = :tv,
                      estado       = 0 
                  WHERE id = :id";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(':final', $monto_final);
        $stmt->bindParam(':tv',    $total_ventas);
        $stmt->bindParam(':id',    $id_caja);
        return $stmt->execute();
    }

    public function getHistorial($fecha_inicio, $fecha_fin) {
        $sql = "SELECT c.*, u.nombre as cajero,
                    (SELECT COALESCE(SUM(v.total),0) FROM ventas v WHERE v.caja_id = c.id AND v.estado = 1) as ventas_turno,
                    (SELECT COALESCE(SUM(g.monto),0) FROM gastos g WHERE g.caja_id = c.id) as gastos_turno,
                    (SELECT COUNT(*) FROM ventas v WHERE v.caja_id = c.id AND v.estado = 1) as num_tickets
                FROM cajas c
                JOIN usuarios u ON c.usuario_id = u.id_usuario
                WHERE date(c.fecha_apertura) BETWEEN :f1 AND :f2
                ORDER BY c.fecha_apertura DESC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':f1', $fecha_inicio);
        $stmt->bindParam(':f2', $fecha_fin);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
