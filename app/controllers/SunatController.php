<?php
require_once '../app/models/Venta.php';

class SunatController {
    private $db;
    private $ventaModel;

    public function __construct($db) {
        $this->db = $db;
        $this->ventaModel = new Venta($db);
    }

    public function enviar() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?route=login"); exit;
        }

        $venta_id = $_GET['id'] ?? 0;
        $venta = $this->ventaModel->getById($venta_id);

        if (!$venta) {
            $_SESSION['sunat_error'] = "Venta no encontrada.";
            header("Location: index.php?route=historial_ventas"); exit;
        }

        // Simular envío a SUNAT
        $resultado = $this->simularEnvioSunat($venta);

        if ($resultado['exito']) {
            $stmt = $this->db->prepare("UPDATE ventas SET estado_sunat = 'ENVIADO', sunat_cdr = ? WHERE id = ?");
            $stmt->execute([$resultado['cdr'], $venta_id]);
            $_SESSION['sunat_ok'] = "✅ Comprobante enviado a SUNAT. CDR: " . $resultado['cdr'];
        } else {
            $stmt = $this->db->prepare("UPDATE ventas SET estado_sunat = 'RECHAZADO' WHERE id = ?");
            $stmt->execute([$venta_id]);
            $_SESSION['sunat_error'] = "❌ Error SUNAT: " . $resultado['mensaje'];
        }

        header("Location: index.php?route=historial_ventas"); exit;
    }

    public function reenviar() {
        if (session_status() == PHP_SESSION_NONE) session_start();
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?route=login"); exit;
        }

        $venta_id = $_GET['id'] ?? 0;
        $venta = $this->ventaModel->getById($venta_id);

        if (!$venta) {
            $_SESSION['sunat_error'] = "Venta no encontrada.";
            header("Location: index.php?route=historial_ventas"); exit;
        }

        if ($venta['estado_sunat'] == 'ENVIADO') {
            $_SESSION['sunat_error'] = "⚠️ Esta venta ya fue enviada a SUNAT.";
            header("Location: index.php?route=historial_ventas"); exit;
        }

        // Reenviar
        $resultado = $this->simularEnvioSunat($venta);

        if ($resultado['exito']) {
            $stmt = $this->db->prepare("UPDATE ventas SET estado_sunat = 'ENVIADO', sunat_cdr = ? WHERE id = ?");
            $stmt->execute([$resultado['cdr'], $venta_id]);
            $_SESSION['sunat_ok'] = "✅ Comprobante reenviado a SUNAT. CDR: " . $resultado['cdr'];
        } else {
            $stmt = $this->db->prepare("UPDATE ventas SET estado_sunat = 'RECHAZADO' WHERE id = ?");
            $stmt->execute([$venta_id]);
            $_SESSION['sunat_error'] = "❌ Error al reenviar: " . $resultado['mensaje'];
        }

        header("Location: index.php?route=historial_ventas"); exit;
    }

    private function simularEnvioSunat($venta) {
        // SIMULACIÓN — reemplazar con lógica real cuando tengas certificado
        sleep(1);
        
        // 95% de éxito, 5% de error aleatorio
        if (rand(1, 100) <= 95) {
            $cdr = 'CDR-' . date('Ymd') . '-' . str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
            return ['exito' => true, 'cdr' => $cdr, 'mensaje' => 'Aceptada'];
        } else {
            return ['exito' => false, 'cdr' => null, 'mensaje' => 'Error de conexión con SUNAT (simulado)'];
        }
    }
}
?>