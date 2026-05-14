<?php
if (class_exists('Serie')) return;

class Serie {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getTodas() {
        $stmt = $this->conn->query("SELECT * FROM series_comprobantes ORDER BY tipo_comprobante, serie ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPorSerie($serie) {
        $stmt = $this->conn->prepare("SELECT * FROM series_comprobantes WHERE serie = ? LIMIT 1");
        $stmt->execute([$serie]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function actualizarCorrelativo($serie, $correlativo) {
        $stmt = $this->conn->prepare("UPDATE series_comprobantes SET correlativo = ? WHERE serie = ?");
        return $stmt->execute([$correlativo, $serie]);
    }
}
?>