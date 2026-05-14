<?php
// Asegurar que el modelo se cargue correctamente
$modelPath = '../app/models/Configuracion.php';
if (!file_exists($modelPath)) {
    die("ERROR: No se encuentra el archivo $modelPath");
}
require_once $modelPath;

if (!class_exists('Configuracion')) {
    die("ERROR: La clase Configuracion no existe en $modelPath");
}

class ConfigController {
    private $db;
    private $configModel;

    public function __construct($db) {
        $this->db = $db;
        $this->configModel = new Configuracion($db);
        
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_rol']) || ($_SESSION['user_rol'] != 'ADMIN' && $_SESSION['user_rol'] != 'Administrador')) {
            header("Location: index.php?route=dashboard");
            exit;
        }
    }

    public function index() {
        $tallas = $this->configModel->getTallas();
        $colores = $this->configModel->getColores();
        require_once '../app/views/config/index.php';
    }

    public function store_talla() {
        header("Location: index.php?route=configuracion");
    }
    public function store_color() {
        header("Location: index.php?route=configuracion");
    }
    public function delete_talla() {
        header("Location: index.php?route=configuracion");
    }
    public function delete_color() {
        header("Location: index.php?route=configuracion");
    }
}
?>