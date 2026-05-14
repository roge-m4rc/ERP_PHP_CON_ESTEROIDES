<?php
class Database {
    private $db_path;
    public $conn;

    public function __construct() {
        // Ruta al archivo SQLite (ajusta según tu estructura)
        $this->db_path = __DIR__ . '/pos_sistema.sqlite';
    }

    public function getConnection() {
        $this->conn = null;
        try {
            // DSN para SQLite
            $this->conn = new PDO("sqlite:" . $this->db_path);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // SQLite no necesita SET NAMES utf8, pero sí forzamos el modo asociativo
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }
        return $this->conn;
    }
}
?>