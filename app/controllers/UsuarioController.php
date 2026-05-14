<?php
require_once '../app/models/Usuario.php';

class UsuarioController {
    private $db;
    private $usuarioModel;

    public function __construct($db) {
        $this->db = $db;
        $this->usuarioModel = new Usuario($db);
        
        if (session_status() == PHP_SESSION_NONE) session_start();
        
        if (!isset($_SESSION['user_rol']) || ($_SESSION['user_rol'] != 'ADMIN' && $_SESSION['user_rol'] != 'Administrador')) {
            header("Location: index.php?route=dashboard");
            exit;
        }
    }

    public function index() {
        $usuarios = $this->usuarioModel->listar();
        require_once '../app/views/usuarios/index.php';
    }

    public function create() {
        require_once '../app/views/usuarios/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $nombre = $_POST['nombre'];
            $usuario = $_POST['usuario'];
            $password = $_POST['password'];
            $rol = $_POST['rol'];

            if ($this->usuarioModel->existeUsuario($usuario)) {
                echo "<script>alert('Ese nombre de usuario ya existe'); window.history.back();</script>";
                return;
            }

            if($this->usuarioModel->crear($nombre, $usuario, $password, $rol)) {
                header("Location: index.php?route=usuarios&ok=1");
            } else {
                echo "Error al crear usuario";
            }
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        $usuario = $this->usuarioModel->getById($id);
        
        if (!$usuario) {
            header("Location: index.php?route=usuarios&error=1");
            exit;
        }
        
        require_once '../app/views/usuarios/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id_usuario'] ?? 0;
            $nombre = $_POST['nombre'] ?? '';
            $usuario = $_POST['usuario'] ?? '';
            $rol = $_POST['rol'] ?? '';
            $estado = $_POST['estado'] ?? 1;
            
            // Si envió password nuevo, actualizarlo
            $password = !empty($_POST['password']) ? $_POST['password'] : null;

            if($this->usuarioModel->actualizar($id, $nombre, $usuario, $rol, $estado, $password)) {
                header("Location: index.php?route=usuarios&ok=2");
            } else {
                header("Location: index.php?route=usuarios&error=1");
            }
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        // No permitir eliminarse a sí mismo
        if ($id == $_SESSION['user_id']) {
            header("Location: index.php?route=usuarios&error=self");
            exit;
        }
        
        if($this->usuarioModel->eliminar($id)) {
            header("Location: index.php?route=usuarios&ok=3");
        } else {
            header("Location: index.php?route=usuarios&error=1");
        }
        exit;
    }
}
?>