<?php
if (class_exists('Dashboard')) return;

class Dashboard {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getVentasHoy() {
        $q = "SELECT COALESCE(SUM(total),0) as total FROM ventas 
              WHERE DATE(fecha) = CURRENT_DATE AND estado = 1";
        return (float)$this->conn->query($q)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getTicketsHoy() {
        $q = "SELECT COUNT(*) as total FROM ventas 
              WHERE DATE(fecha) = CURRENT_DATE AND estado = 1";
        return (int)$this->conn->query($q)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getTotalProductos() {
        $q = "SELECT COUNT(*) as total FROM productos WHERE estado = 1";
        return (int)$this->conn->query($q)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getStockBajo() {
        $q = "SELECT COUNT(*) as total FROM variantes WHERE stock < 5 AND stock >= 0";
        return (int)$this->conn->query($q)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getVentasSemana() {
        $q = "SELECT DATE(fecha) as dia, SUM(total) as total 
            FROM ventas 
            WHERE estado = 1 
            AND DATE(fecha) >= CURRENT_DATE - INTERVAL '6 days'
            GROUP BY DATE(fecha) 
            ORDER BY DATE(fecha) ASC";
        $stmt = $this->conn->prepare($q);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getVentasMes() {
        $q = "SELECT SUM(total) as total 
              FROM ventas 
              WHERE estado = 1 
              AND DATE(fecha) >= CURRENT_DATE - INTERVAL '30 days'";
        return (float)$this->conn->query($q)->fetch(PDO::FETCH_ASSOC)['total'];
    }

    public function getVentasRecientes($limite = 5) {
        // CORRECCIÓN AQUÍ: DATE(v.fecha) = CURRENT_DATE
        $q = "SELECT v.*, u.nombre as vendedor 
              FROM ventas v JOIN usuarios u ON v.usuario_id = u.id_usuario
              WHERE v.estado = 1 AND DATE(v.fecha) = CURRENT_DATE
              ORDER BY v.fecha DESC LIMIT :lim";
        $stmt = $this->conn->prepare($q);
        $stmt->bindParam(':lim', $limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>