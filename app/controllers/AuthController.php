<?php
require_once '../app/models/Usuario.php';

class AuthController {
    private $db;
    private $usuario;

    public function __construct($db) {
        $this->db = $db;
        $this->usuario = new Usuario($db);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $usr = $_POST['usuario'];
            $pass = $_POST['password'];
            
            if ($this->usuario->login($usr, $pass)) {
                // Estandarizar nombres de sesión
                $_SESSION['user_id'] = $this->usuario->id_usuario;
                $_SESSION['user_nombre'] = $this->usuario->nombre;
                $_SESSION['user_rol'] = $this->usuario->rol;
                header("Location: index.php?route=dashboard");
                exit();
            } else {
                header("Location: index.php?route=login&error=1");
                exit();
            }
        } else {
            // Mostrar vista de login
            require_once '../app/views/auth/login.php';
        }
    }

    public function logout() {
        session_destroy();
        header("Location: index.php?route=login");
        exit();
    }
}
?>