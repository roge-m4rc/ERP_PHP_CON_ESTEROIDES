<?php
require_once '../app/models/Empresa.php';
require_once '../app/models/Serie.php';

class ConfigEmpresaController {
    private $db;
    private $empresaModel;
    private $serieModel;

    public function __construct($db) {
        $this->db = $db;
        $this->empresaModel = new Empresa($db);
        $this->serieModel = new Serie($db);
    }

    public function index() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?route=login"); exit;
        }
        if ($_SESSION['user_rol'] != 'ADMIN') {
            header("Location: index.php?route=dashboard"); exit;
        }

        $empresa = $this->empresaModel->getConfig();
        $series = $this->serieModel->getTodas();

        require_once '../app/views/configuracion/empresa.php';
    }

    public function guardar() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'ADMIN') {
            header("Location: index.php?route=login"); exit;
        }

        $data = [
            'ruc' => $_POST['ruc'] ?? '',
            'razon_social' => $_POST['razon_social'] ?? '',
            'direccion' => $_POST['direccion'] ?? '',
            'sol_usuario' => $_POST['sol_usuario'] ?? '',
            'sol_clave' => $_POST['sol_clave'] ?? '',
            'telefono' => $_POST['telefono'] ?? '',
            'email' => $_POST['email'] ?? ''
        ];

        $this->empresaModel->guardar($data);
        header("Location: index.php?route=config_empresa&ok=1");
        exit;
    }

    public function guardarCorrelativos() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'ADMIN') {
            header("Location: index.php?route=login"); exit;
        }

        if (!empty($_POST['series'])) {
            foreach ($_POST['series'] as $serie => $correlativo) {
                $correlativo = max(1, intval($correlativo));
                $this->serieModel->actualizarCorrelativo($serie, $correlativo);
            }
        }

        header("Location: index.php?route=config_empresa&ok=2");
        exit;
    }

    public function subirCertificado() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id']) || $_SESSION['user_rol'] != 'ADMIN') {
            header("Location: index.php?route=login"); exit;
        }

        if (isset($_FILES['certificado']) && $_FILES['certificado']['error'] == 0) {
            $uploadDir = '../app/config/certificados/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
            
            $filename = 'certificado_' . time() . '.pem';
            $ruta = $uploadDir . $filename;
            
            if (move_uploaded_file($_FILES['certificado']['tmp_name'], $ruta)) {
                $this->empresaModel->guardarCertificado($ruta);
                header("Location: index.php?route=config_empresa&ok=3");
                exit;
            }
        }

        header("Location: index.php?route=config_empresa&error=1");
        exit;
    }
}
?>