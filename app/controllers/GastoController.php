<?php
require_once '../app/models/Gasto.php';
require_once '../app/models/Caja.php';

class GastoController {
    private $db;
    private $gastoModel;
    private $cajaModel;

    public function __construct($db) {
        $this->db         = $db;
        $this->gastoModel = new Gasto($db);
        $this->cajaModel  = new Caja($db);
        if (session_status() == PHP_SESSION_NONE) session_start();
    }

    public function registrar() {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: index.php?route=dashboard"); exit;
        }
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?route=login"); exit;
        }

        $usuario_id = $_SESSION['user_id'];
        $caja = $this->cajaModel->obtenerCajaAbierta($usuario_id);
        if (!$caja) {
            echo "<script>alert('⚠️ No tienes caja abierta.'); window.location.href='index.php?route=caja_apertura';</script>";
            return;
        }

        $monto       = floatval($_POST['monto'] ?? 0);
        $descripcion = htmlspecialchars(trim($_POST['descripcion'] ?? 'Sin descripción'));

        if ($monto <= 0) {
            echo "<script>alert('El monto debe ser mayor a 0.'); window.history.back();</script>"; return;
        }
        if (empty($descripcion)) {
            echo "<script>alert('Debes escribir una descripción.'); window.history.back();</script>"; return;
        }

        if ($this->gastoModel->crear($caja['id'], $usuario_id, $monto, $descripcion)) {
            $origen = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'index.php?route=dashboard';
            header("Location: $origen&gasto=ok");
        } else {
            echo "<script>alert('Error al registrar el gasto.'); window.history.back();</script>";
        }
        exit;
    }

    public function eliminar() {
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?route=login"); exit; }
        $id      = intval($_GET['id'] ?? 0);
        $usuario = $_SESSION['user_id'];
        $caja    = $this->cajaModel->obtenerCajaAbierta($usuario);
        if ($caja && $id) {
            $this->gastoModel->eliminar($id, $caja['id']);
        }
        header("Location: index.php?route=caja_cierre");
        exit;
    }
}
?>
