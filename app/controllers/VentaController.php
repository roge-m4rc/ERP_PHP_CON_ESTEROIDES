<?php
require_once '../app/models/Venta.php';
require_once '../app/models/Caja.php';

class VentaController {
    private $db;
    private $ventaModel;
    private $cajaModel;

    public function __construct($db) {
        $this->db         = $db;
        $this->ventaModel = new Venta($db);
        $this->cajaModel  = new Caja($db);
        if (session_status() == PHP_SESSION_NONE) session_start();
        date_default_timezone_set('America/Lima');
    }

    private function verificarPermisoDeVenta() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?route=login"); exit;
        }
        $caja = $this->cajaModel->obtenerCajaAbierta($_SESSION['user_id']);
        if (!$caja) {
            echo "<script>alert('⚠️ Debes APERTURAR CAJA antes de vender.'); window.location.href='index.php?route=caja_apertura';</script>";
            exit;
        }
        $fecha_apertura = date('Y-m-d', strtotime($caja['fecha_apertura']));
        $hoy = date('Y-m-d');
        if ($fecha_apertura < $hoy) {
            $this->cajaModel->cerrar($caja['id'], 0, 0);
            echo "<script>alert('ℹ️ Se cerró automáticamente una caja del día anterior. Abre una nueva caja.'); window.location.href='index.php?route=caja_apertura';</script>";
            exit;
        }
        return $caja;
    }

    public function index() {
        $this->verificarPermisoDeVenta();
        
        $termino = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
        
        // Si hay término de búsqueda, buscar. Si no, cargar todos los disponibles
        if (!empty($termino)) {
            $resultados = $this->ventaModel->buscarProducto($termino);
        } else {
            $resultados = $this->ventaModel->listarProductosDisponibles(24);
        }
        
        $total_venta = 0;
        if (isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as $item) {
                $total_venta += $item['subtotal'];
            }
        }
        
        require_once '../app/views/ventas/nueva.php';
    }

    public function agregar() {
        $this->verificarPermisoDeVenta();
        
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id       = intval($_POST['id_variante']);
            $nombre   = htmlspecialchars($_POST['nombre_completo']);
            $precio   = floatval($_POST['precio']);
            $cantidad = intval($_POST['cantidad'] ?? 1);
            
            if ($cantidad < 1) {
                $cantidad = 1;
            }

            if (!isset($_SESSION['carrito'])) {
                $_SESSION['carrito'] = [];
            }

            $encontrado = false;
            foreach ($_SESSION['carrito'] as $key => $item) {
                if ($item['id'] == $id) {
                    $_SESSION['carrito'][$key]['cantidad'] += $cantidad;
                    $_SESSION['carrito'][$key]['subtotal'] = $_SESSION['carrito'][$key]['cantidad'] * $_SESSION['carrito'][$key]['precio'];
                    $encontrado = true;
                    break;
                }
            }
            
            if (!$encontrado) {
                $_SESSION['carrito'][] = [
                    'id'       => $id,
                    'nombre'   => $nombre,
                    'precio'   => $precio,
                    'cantidad' => $cantidad,
                    'subtotal' => $precio * $cantidad
                ];
            }
        }
        
        $search = isset($_POST['search_term']) ? $_POST['search_term'] : '';
        header("Location: index.php?route=nueva_venta&buscar=" . urlencode($search));
        exit;
    }

    public function quitar() {
        if (isset($_GET['idx']) && isset($_SESSION['carrito'])) {
            $idx = intval($_GET['idx']);
            if (isset($_SESSION['carrito'][$idx])) {
                array_splice($_SESSION['carrito'], $idx, 1);
            }
        }
        $search = isset($_GET['buscar']) ? $_GET['buscar'] : '';
        header("Location: index.php?route=nueva_venta&buscar=" . urlencode($search));
        exit;
    }

    public function limpiar() {
        $_SESSION['carrito'] = [];
        header("Location: index.php?route=nueva_venta");
        exit;
    }

    public function finalizar() {
        $this->verificarPermisoDeVenta();
        if (empty($_SESSION['carrito'])) {
            header("Location: index.php?route=nueva_venta"); return;
        }

        $total_carrito = 0;
        foreach ($_SESSION['carrito'] as $item) $total_carrito += $item['subtotal'];

        $tipo_comprobante = $_POST['tipo_comprobante'] ?? '03';
        $cliente_tipo_doc = $_POST['cliente_tipo_doc'] ?? '1';
        $cliente_num_doc  = htmlspecialchars($_POST['cliente_num_doc'] ?? '00000000');
        $cliente_nombre   = htmlspecialchars($_POST['cliente_nombre'] ?? 'Público General');
        $metodo_pago      = $_POST['metodo_pago'] ?? 'EFECTIVO';

        // Validar documento mínimo
        if ($cliente_num_doc == '-' || empty($cliente_num_doc)) {
            $cliente_num_doc = '00000000';
            $cliente_nombre = 'Público General';
        }

        $resultado = $this->ventaModel->registrarVenta(
            $_SESSION['user_id'],
            $tipo_comprobante,
            $cliente_tipo_doc,
            $cliente_num_doc,
            $cliente_nombre,
            $total_carrito,
            $_SESSION['carrito'],
            $metodo_pago
        );

        if ($resultado['ok']) {
            $_SESSION['carrito'] = [];
            $id_venta = $resultado['id'];

            // Intentar SUNAT solo si hay configuración
            require_once '../app/sunat/SunatHelper.php';
            $ventaData = $this->ventaModel->getVentaById($id_venta);
            $detallesData = $this->ventaModel->getDetalleVenta($id_venta);
            
            $stmtCfg = $this->db->query("SELECT 
            ruc,
            razon_social as nombre_empresa,
            direccion,
            telefono,
            email
            FROM configuracion_empresa LIMIT 1");
            $configData = $stmtCfg->fetch(PDO::FETCH_ASSOC);

            $sunatRes = SunatHelper::emitirComprobante($ventaData, $detallesData, $configData);
            
            // Actualizar estado_sunat
            $stmtUpd = $this->db->prepare("UPDATE ventas SET estado_sunat = :est WHERE id = :id");
            $stmtUpd->execute([':est' => $sunatRes['estado_sunat'], ':id' => $id_venta]);

            // Mensaje según resultado
            if ($sunatRes['xml_generado']) {
                $alerta = $sunatRes['ok'] 
                    ? '✅ Venta registrada y enviada a SUNAT' 
                    : '⚠️ Venta registrada. SUNAT: ' . $sunatRes['msg'];
            } else {
                $alerta = '✅ Venta registrada correctamente. ' . $sunatRes['msg'];
            }

            echo "<script>
                alert('$alerta');
                var imprimir = confirm('¿Imprimir Ticket?');
                if (imprimir) { window.open('index.php?route=ver_ticket&id=$id_venta', '_blank'); }
                window.location.href = 'index.php?route=nueva_venta';
            </script>";
        } else {
            $msg = $resultado['msg'] ?? 'Error desconocido';
            echo "<script>alert('❌ Error de BD: $msg'); window.history.back();</script>";
        }
    }

    public function historial() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?route=login"); exit;
        }
        $filtro = [];
        if (!empty($_GET['f_ini'])) $filtro['fecha_inicio'] = $_GET['f_ini'];
        if (!empty($_GET['f_fin'])) $filtro['fecha_fin']    = $_GET['f_fin'];
        $ventas = $this->ventaModel->listarVentas($filtro);
        require_once '../app/views/ventas/historial.php';
    }
    public function exportarHistorial() {
        if (!isset($_SESSION['user_id'])) {
            header("Location: index.php?route=login"); exit;
        }
        
        $filtro = [];
        if (!empty($_GET['f_ini'])) $filtro['fecha_inicio'] = $_GET['f_ini'];
        if (!empty($_GET['f_fin'])) $filtro['fecha_fin']    = $_GET['f_fin'];
        $ventas = $this->ventaModel->listarVentas($filtro);

        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename=ventas_' . date('Ymd_His') . '.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo chr(0xEF) . chr(0xBB) . chr(0xBF);

        echo '<table border="1">';
        echo '<tr style="background-color:#4472C4;color:white;font-weight:bold;">';
        echo '<th>ID</th>';
        echo '<th>Serie-Correlativo</th>';
        echo '<th>Fecha</th>';
        echo '<th>Cliente</th>';
        echo '<th>Doc Cliente</th>';
        echo '<th>Vendedor</th>';
        echo '<th>Método Pago</th>';
        echo '<th>IGV</th>';
        echo '<th>Total</th>';
        echo '<th>Estado</th>';
        echo '<th>Estado SUNAT</th>';
        echo '</tr>';

        $total_general = 0;
        foreach ($ventas as $v) {
            $anulado = ($v['estado'] == 0);
            if (!$anulado) $total_general += $v['total'];

            echo '<tr' . ($anulado ? ' style="background-color:#ffcccc;"' : '') . '>';
            echo '<td>' . $v['id'] . '</td>';
            echo '<td>' . $v['serie'] . '-' . $v['correlativo'] . '</td>';
            echo '<td>' . date("d/m/Y H:i", strtotime($v['fecha'])) . '</td>';
            echo '<td>' . htmlspecialchars($v['cliente_nombre'] ?? 'General') . '</td>';
            echo '<td>' . ($v['cliente_tipo_doc'] ?? '') . ': ' . ($v['cliente_num_doc'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($v['vendedor'] ?? '') . '</td>';
            echo '<td>' . ($v['metodo_pago'] ?? '') . '</td>';
            echo '<td style="text-align:right;">S/ ' . number_format($v['igv'] ?? 0, 2) . '</td>';
            echo '<td style="text-align:right;">S/ ' . number_format($v['total'], 2) . '</td>';
            echo '<td>' . ($anulado ? 'ANULADO' : 'ACTIVO') . '</td>';
            echo '<td>' . ($v['estado_sunat'] ?? 'REGISTRADO') . '</td>';
            echo '</tr>';
        }

        echo '<tr style="background-color:#70AD47;color:white;font-weight:bold;">';
        echo '<td colspan="8" style="text-align:right;">TOTAL ACTIVAS:</td>';
        echo '<td style="text-align:right;">S/ ' . number_format($total_general, 2) . '</td>';
        echo '<td colspan="2"></td>';
        echo '</tr>';

        echo '</table>';
        exit;
    }

    public function ver_ticket() {
        if (!isset($_GET['id'])) { header("Location: index.php?route=nueva_venta"); exit; }
        $id_venta = intval($_GET['id']);
        $venta    = $this->ventaModel->getVentaById($id_venta);
        $detalles = $this->ventaModel->getDetalleVenta($id_venta);
        if (!$venta) { echo "Ticket no encontrado."; exit; }

        // ✅ CAMBIO: Leer de configuracion_empresa (SUNAT) en vez de configuracion
         $stmtCfg = $this->db->query("SELECT 
            ruc,
            razon_social as nombre_empresa,
            direccion,
            telefono,
            email
            FROM configuracion_empresa LIMIT 1");
        $config  = $stmtCfg->fetch(PDO::FETCH_ASSOC) ?: [];

        require_once '../app/views/ventas/ticket.php';
    }

    public function anular() {
        if (!isset($_SESSION['user_id'])) { header("Location: index.php?route=login"); exit; }
        if ($_SESSION['user_rol'] != 'ADMIN') {
            echo "<script>alert('Solo administradores pueden anular ventas.'); window.history.back();</script>"; exit;
        }
        $id = intval($_GET['id']);
        if ($this->ventaModel->anularVenta($id)) {
            header("Location: index.php?route=historial_ventas&msg=anulado");
        } else {
            echo "<script>alert('Error al anular la venta.'); window.history.back();</script>";
        }
    }

    public function consulta_api() {
        header('Content-Type: application/json');
        
        $tipo = $_GET['tipo'] ?? '1'; // 1 = DNI, 6 = RUC
        $documento = $_GET['doc'] ?? '';

        if (empty($documento)) {
            echo json_encode(['success' => false, 'msg' => 'Documento vacío']);
            exit;
        }

        // --- PASO A: Búsqueda Local (Tu Base de Datos) ---
        $stmt = $this->db->prepare("SELECT nombre FROM clientes WHERE num_doc = :doc AND tipo_doc = :tipo LIMIT 1");
        $stmt->execute([':doc' => $documento, ':tipo' => $tipo]);
        $clienteLocal = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($clienteLocal) {
            echo json_encode([
                'success' => true, 
                'nombre' => $clienteLocal['nombre'],
                'fuente' => 'LOCAL'
            ]);
            exit;
        }

        // --- PASO B: Búsqueda Externa (API SUNAT/RENIEC) ---
        $token = 'TU_TOKEN_AQUI'; // Regístrate en apis.net.pe para obtener uno
        $url = ($tipo === '1') 
            ? "https://api.apis.net.pe/v1/dni?numero=" . $documento 
            : "https://api.apis.net.pe/v1/ruc?numero=" . $documento;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        // curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]); // Si usas token
        $respuesta = curl_exec($ch);
        $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($http_status == 200) {
            $data = json_decode($respuesta, true);
            $nombre = $data['nombre'];

            // --- PASO C: Registro Automático ---
            // Guardamos al nuevo cliente para que la próxima vez sea una consulta LOCAL
            try {
                $ins = $this->db->prepare("INSERT INTO clientes (tipo_doc, num_doc, nombre) VALUES (:t, :n, :nom)");
                $ins->execute([
                    ':t' => $tipo,
                    ':n' => $documento,
                    ':nom' => $nombre
                ]);
            } catch (Exception $e) {
                // Si ya existe por algún motivo, simplemente ignoramos el error de insert
            }

            echo json_encode([
                'success' => true, 
                'nombre' => $nombre,
                'fuente' => 'API_NUEVO'
            ]);
        } else {
            echo json_encode(['success' => false, 'msg' => 'No encontrado en SUNAT']);
        }
        exit;
    }
    public function descargar_pdf() {
        if (!isset($_GET['id'])) exit;
        $id_venta = intval($_GET['id']);
        
        // 1. Obtener datos (lo mismo que haces en ver_ticket)
        $venta = $this->ventaModel->getVentaById($id_venta);
        $detalles = $this->ventaModel->getDetalleVenta($id_venta);
        $stmtCfg = $this->db->query("SELECT * FROM configuracion LIMIT 1");
        $config = $stmtCfg->fetch(PDO::FETCH_ASSOC);

        // 2. Cargar Dompdf
        require_once '../vendor/autoload.php';
        $dompdf = new \Dompdf\Dompdf();

        // 3. Renderizar la vista en una variable
        ob_start();
        require_once '../app/views/ventas/ticket_pdf.php'; // Una versión sin botones, solo diseño
        $html = ob_get_clean();

        // 4. Configurar y generar
        $dompdf->loadHtml($html);
        $dompdf->setPaper([0, 0, 226, 600], 'portrait'); // Tamaño térmico 80mm aprox.
        $dompdf->render();

        // 5. Salida al navegador
        $nombre_archivo = "Comprobante-{$venta['serie']}-{$venta['correlativo']}.pdf";
        $dompdf->stream($nombre_archivo, ["Attachment" => false]); // false para ver, true para descargar
        exit;
    }
}
?>