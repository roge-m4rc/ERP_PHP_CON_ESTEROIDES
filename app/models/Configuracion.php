<?php
if (class_exists('Configuracion')) return;

class Configuracion {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
    }

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

    public function guardarTalla($nombre) { return true; }
    public function eliminarTalla($id) { return true; }
    public function guardarColor($nombre) { return true; }
    public function eliminarColor($id) { return true; }
}
?>