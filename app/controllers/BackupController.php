<?php
class BackupController {
    private $db_path;

    public function __construct() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_rol']) || ($_SESSION['user_rol'] != 'ADMIN' && $_SESSION['user_rol'] != 'Administrador')) {
            header("Location: index.php?route=dashboard"); 
            exit;
        }
        $this->db_path = __DIR__ . '/../config/pos_sistema.sqlite';
    }

    public function descargar() {
        if (!file_exists($this->db_path)) {
            die("Error: No se encontró la base de datos.");
        }

        $fecha = date("Y-m-d_H-i");
        $filename = "backup_machos_$fecha.sqlite";
        
        header('Content-Type: application/octet-stream');
        header("Content-Transfer-Encoding: Binary");
        header("Content-disposition: attachment; filename=\"$filename\"");
        header('Content-Length: ' . filesize($this->db_path));
        
        readfile($this->db_path);
        exit;
    }
}
?>