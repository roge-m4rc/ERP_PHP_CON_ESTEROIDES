<?php
require_once '../app/models/Venta.php';
require_once '../app/models/Caja.php';
require_once '../app/models/Gasto.php';

class ReporteController {
    private $conn;

    public function __construct($db) {
        $this->conn = $db;
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_rol']) || !in_array($_SESSION['user_rol'], ['ADMIN', 'Administrador'])) {
            header("Location: index.php?route=dashboard"); exit;
        }
        date_default_timezone_set('America/Lima');
    }

    public function index() {
        $fecha_inicio = isset($_GET['f_inicio']) ? $_GET['f_inicio'] : date('Y-m-01');
        $fecha_fin    = isset($_GET['f_fin'])    ? $_GET['f_fin']    : date('Y-m-d');

        // === VENTAS DEL PERÍODO ===
        $sql = "SELECT v.*, u.nombre as vendedor 
                FROM ventas v 
                JOIN usuarios u ON v.usuario_id = u.id_usuario
                WHERE v.estado = 1 
                AND date(v.fecha) BETWEEN :f_inicio AND :f_fin
                ORDER BY v.fecha ASC";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':f_inicio', $fecha_inicio);
        $stmt->bindParam(':f_fin',    $fecha_fin);
        $stmt->execute();
        $ventas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_efectivo  = 0;
        $total_yape      = 0;
        $total_tarjeta   = 0;
        $total_general   = 0;
        $cantidad_ventas = 0;

        foreach ($ventas as $v) {
            $total_general += $v['total'];
            $cantidad_ventas++;
            if ($v['metodo_pago'] == 'EFECTIVO')       $total_efectivo += $v['total'];
            elseif ($v['metodo_pago'] == 'YAPE')        $total_yape     += $v['total'];
            elseif ($v['metodo_pago'] == 'TARJETA')     $total_tarjeta  += $v['total'];
        }

        // === GASTOS DEL PERÍODO ===
        $sqlGastos = "SELECT COALESCE(SUM(monto),0) as total FROM gastos 
                      WHERE date(fecha) BETWEEN :f1 AND :f2";
        $stmtG = $this->conn->prepare($sqlGastos);
        $stmtG->bindParam(':f1', $fecha_inicio);
        $stmtG->bindParam(':f2', $fecha_fin);
        $stmtG->execute();
        $total_gastos_periodo = $stmtG->fetch(PDO::FETCH_ASSOC)['total'];

        // === LISTA DE GASTOS ===
        $sqlListaGastos = "SELECT g.*, u.nombre as usuario_nombre 
                           FROM gastos g 
                           JOIN usuarios u ON g.usuario_id = u.id_usuario
                           WHERE date(g.fecha) BETWEEN :f1 AND :f2
                           ORDER BY g.fecha DESC";
        $stmtLG = $this->conn->prepare($sqlListaGastos);
        $stmtLG->bindParam(':f1', $fecha_inicio);
        $stmtLG->bindParam(':f2', $fecha_fin);
        $stmtLG->execute();
        $lista_gastos = $stmtLG->fetchAll(PDO::FETCH_ASSOC);

        // === HISTORIAL DE CAJAS (con subquery correcta) ===
        $sqlCajas = "SELECT c.*, u.nombre as cajero,
                        (SELECT COALESCE(SUM(v.total),0) FROM ventas v WHERE v.caja_id = c.id AND v.estado = 1) as ventas_turno,
                        (SELECT COALESCE(SUM(g.monto),0) FROM gastos g WHERE g.caja_id = c.id) as gastos_turno,
                        (SELECT COUNT(*) FROM ventas v WHERE v.caja_id = c.id AND v.estado = 1) as num_tickets
                     FROM cajas c
                     JOIN usuarios u ON c.usuario_id = u.id_usuario
                     WHERE date(c.fecha_apertura) BETWEEN :f_inicio AND :f_fin
                     ORDER BY c.fecha_apertura DESC";
        $stmtC = $this->conn->prepare($sqlCajas);
        $stmtC->bindParam(':f_inicio', $fecha_inicio);
        $stmtC->bindParam(':f_fin',    $fecha_fin);
        $stmtC->execute();
        $historial_cajas = $stmtC->fetchAll(PDO::FETCH_ASSOC);

        // === GRÁFICO: Ventas por día ===
        $sqlGrafico = "SELECT date(fecha) as dia, COALESCE(SUM(total),0) as total, COUNT(*) as cantidad
                       FROM ventas WHERE estado = 1
                       AND date(fecha) BETWEEN :f1 AND :f2
                       GROUP BY date(fecha) ORDER BY dia ASC";
        $stmtGraf = $this->conn->prepare($sqlGrafico);
        $stmtGraf->bindParam(':f1', $fecha_inicio);
        $stmtGraf->bindParam(':f2', $fecha_fin);
        $stmtGraf->execute();
        $datos_grafico = $stmtGraf->fetchAll(PDO::FETCH_ASSOC);

        // === PRODUCTOS MÁS VENDIDOS ===
        $sqlTopProductos = "SELECT p.nombre, SUM(d.cantidad) as total_vendido, SUM(d.total) as monto_total
                            FROM detalle_ventas d
                            JOIN productos p ON d.producto_id = p.id
                            JOIN ventas v ON d.venta_id = v.id
                            WHERE v.estado = 1 AND date(v.fecha) BETWEEN :f1 AND :f2
                            GROUP BY d.producto_id
                            ORDER BY total_vendido DESC
                            LIMIT 5";
        $stmtTop = $this->conn->prepare($sqlTopProductos);
        $stmtTop->bindParam(':f1', $fecha_inicio);
        $stmtTop->bindParam(':f2', $fecha_fin);
        $stmtTop->execute();
        $top_productos = $stmtTop->fetchAll(PDO::FETCH_ASSOC);

        // Config empresa
        $stmtCfg = $this->conn->query("SELECT 
            ruc,
            razon_social as nombre_empresa,
            direccion,
            telefono,
            email
            FROM configuracion_empresa LIMIT 1");
        $config  = $stmtCfg->fetch(PDO::FETCH_ASSOC) ?: ['nombre_empresa' => 'MACHO\'S BOUTIQUE', 'ruc' => ''];

        require_once '../app/views/reportes/index.php';
    }
}
?>
