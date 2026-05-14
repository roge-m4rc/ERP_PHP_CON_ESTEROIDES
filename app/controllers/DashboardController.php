<?php
require_once '../app/models/Dashboard.php';

class DashboardController {
    private $db;
    private $dashboardModel;

    public function __construct($db) {
        $this->db = $db;
        $this->dashboardModel = new Dashboard($db);
    }

    public function index() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?route=login"); exit;
        }
        date_default_timezone_set('America/Lima');

        $ventas_hoy      = $this->dashboardModel->getVentasHoy();
        $tickets_hoy     = $this->dashboardModel->getTicketsHoy();
        $total_productos = $this->dashboardModel->getTotalProductos();
        $stock_bajo      = $this->dashboardModel->getStockBajo();
        $datos_grafico   = $this->dashboardModel->getVentasSemana();
        $ventas_recientes = $this->dashboardModel->getVentasRecientes(5);

        require_once '../app/views/dashboard.php';
    }
}
?>
