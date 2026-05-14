<?php
if (class_exists('CajaController')) return;

require_once '../app/models/Caja.php';
require_once '../app/models/Gasto.php';

class CajaController {
    private $db;
    private $cajaModel;
    private $gastoModel;

    public function __construct($db) {
        $this->db         = $db;
        $this->cajaModel  = new Caja($db);
        $this->gastoModel = new Gasto($db);
        if (session_status() == PHP_SESSION_NONE) session_start();
    }

    private function requireLogin() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?route=login"); exit;
        }
    }

    public function apertura() {
        $this->requireLogin();
        // Si ya tiene caja abierta, redirigir al dashboard
        $caja = $this->cajaModel->obtenerCajaAbierta($_SESSION['user_id']);
        if ($caja) {
            header("Location: index.php?route=dashboard&msg=caja_ya_abierta"); exit;
        }
        require_once '../app/views/caja/apertura.php';
    }

    public function guardar_apertura() {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: index.php?route=caja_apertura"); exit;
        }
        $monto = floatval($_POST['monto_inicial'] ?? 0);
        if ($monto < 0) $monto = 0;

        $resultado = $this->cajaModel->abrir($_SESSION['user_id'], $monto);
        if ($resultado) {
            header("Location: index.php?route=dashboard&msg=caja_abierta");
        } else {
            header("Location: index.php?route=caja_apertura&error=ya_abierta");
        }
        exit;
    }

    public function cierre() {
        $this->requireLogin();
        $caja = $this->cajaModel->obtenerCajaAbierta($_SESSION['user_id']);
        if (!$caja) {
            echo "<script>alert('No tienes caja abierta.'); window.location.href='index.php?route=dashboard';</script>";
            exit;
        }

        $totales         = $this->cajaModel->calcularTotales($caja['id']);
        $total_gastos    = $this->gastoModel->getTotalGastos($caja['id']);
        $lista_gastos    = $this->gastoModel->listarPorCaja($caja['id']);
        $venta_efectivo  = $totales['venta_efectivo'];

        // Lo que debería haber físicamente en el cajón
        $total_esperado_en_cajon = $caja['monto_inicial'] + $venta_efectivo - $total_gastos;

        require_once '../app/views/caja/cierre.php';
    }

    public function guardar_cierre() {
        $this->requireLogin();
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            header("Location: index.php?route=caja_cierre"); exit;
        }

        $id_caja    = intval($_POST['id_caja']);
        $monto_final = floatval($_POST['monto_final']);

        $caja    = $this->cajaModel->obtenerCajaAbierta($_SESSION['user_id']);
        if (!$caja || $caja['id'] != $id_caja) {
            echo "<script>alert('Error: caja no válida.'); window.history.back();</script>";
            exit;
        }

        $totales = $this->cajaModel->calcularTotales($id_caja);
        $this->cajaModel->cerrar($id_caja, $totales['venta_total'], $monto_final);

        header("Location: index.php?route=dashboard&cerrada=1");
        exit;
    }
}
?>
