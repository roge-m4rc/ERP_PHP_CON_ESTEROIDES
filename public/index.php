<?php
session_start();
date_default_timezone_set('America/Lima');

require_once '../app/config/Database.php';
require_once '../app/controllers/AuthController.php';
require_once '../app/controllers/CategoriaController.php';
require_once '../app/controllers/ProductoController.php';
require_once '../app/controllers/VentaController.php';
require_once '../app/controllers/DashboardController.php';
require_once '../app/controllers/CajaController.php';
require_once '../app/controllers/UsuarioController.php';
require_once '../app/controllers/ConfigController.php';
require_once '../app/controllers/GastoController.php';
require_once '../app/controllers/ReporteController.php';
require_once '../app/controllers/BackupController.php';
require_once '../app/controllers/ConfigEmpresaController.php';  // <-- NUEVO
require_once '../app/controllers/SunatController.php'; 

$database = new Database();
$db = $database->getConnection();


if (!$db) {
    die("<div style='font-family:sans-serif;padding:40px;color:red;'><h2>❌ Error de conexión a la base de datos</h2><p>Verifica que el archivo SQLite existe y tiene permisos de escritura.</p></div>");
}

// Habilitar WAL mode para SQLite (mejor concurrencia)
$db->exec("PRAGMA journal_mode=DELETE");
$db->exec("PRAGMA foreign_keys=ON");

$route = isset($_GET['route']) ? trim($_GET['route']) : 'login';

// Redirigir si ya está logueado
if (isset($_SESSION['user_id']) && $route == 'login') {
    header("Location: index.php?route=dashboard"); exit;
}

switch ($route) {

    // --- AUTH ---
    case 'login':
        $auth = new AuthController($db); $auth->login(); break;
    case 'logout':
        $auth = new AuthController($db); $auth->logout(); break;

    // --- DASHBOARD ---
    case 'dashboard':
        $c = new DashboardController($db); $c->index(); break;

    // --- CATEGORÍAS ---
    case 'categorias':
        $c = new CategoriaController($db); $c->index(); break;
    case 'nueva_categoria':
        $c = new CategoriaController($db); $c->create(); break;
    case 'guardar_categoria':
        $c = new CategoriaController($db); $c->store(); break;
    case 'editar_categoria':
        $c = new CategoriaController($db); $c->edit(); break;
    case 'actualizar_categoria':
        $c = new CategoriaController($db); $c->update(); break;
    case 'eliminar_categoria':
        $c = new CategoriaController($db); $c->delete(); break;

    // --- PRODUCTOS ---
    case 'productos':
        $c = new ProductoController($db); $c->index(); break;
    case 'nuevo_producto':
        $c = new ProductoController($db); $c->create(); break;
    case 'guardar_producto':
        $c = new ProductoController($db); $c->store(); break;
    case 'editar_producto':
        $c = new ProductoController($db); $c->edit(); break;
    case 'actualizar_producto':
        $c = new ProductoController($db); $c->update(); break;
    case 'eliminar_producto':
        $c = new ProductoController($db); $c->delete(); break;
    case 'exportar_productos':
        $c = new ProductoController($db); $c->exportarExcel(); break;
    
    // --- VARIANTES ---
    case 'gestionar_variantes':
        $c = new ProductoController($db); $c->variantes(); break;
    case 'guardar_variante':
        $c = new ProductoController($db); $c->guardar_variante(); break;
    case 'eliminar_variante':
        $c = new ProductoController($db); $c->eliminar_variante(); break;
    case 'actualizar_stock_manual':
        $c = new ProductoController($db); $c->actualizar_stock_manual(); break;

    // --- VENTAS ---
    case 'nueva_venta':
        $c = new VentaController($db); $c->index(); break;
    case 'agregar_carrito':
        $c = new VentaController($db); $c->agregar(); break;
    case 'quitar_carrito':
        $c = new VentaController($db); $c->quitar(); break;
    case 'limpiar_carrito':
        $c = new VentaController($db); $c->limpiar(); break;
    case 'finalizar_venta':
        $c = new VentaController($db); $c->finalizar(); break;
    case 'historial_ventas':
        $c = new VentaController($db); $c->historial(); break;
    case 'exportar_historial':
        $c = new VentaController($db); $c->exportarHistorial(); break;
    case 'ver_ticket':
        $c = new VentaController($db); $c->ver_ticket(); break;
    case 'anular_venta':
        $c = new VentaController($db); $c->anular(); break;
    case 'consulta_api':
        $c = new VentaController($db); $c->consulta_api(); break;

    // --- CAJA ---
    case 'caja_apertura':
        $c = new CajaController($db); $c->apertura(); break;
    case 'guardar_apertura':
        $c = new CajaController($db); $c->guardar_apertura(); break;
    case 'caja_cierre':
        $c = new CajaController($db); $c->cierre(); break;
    case 'guardar_cierre':
        $c = new CajaController($db); $c->guardar_cierre(); break;

    // --- GASTOS ---
    case 'guardar_gasto':
        $c = new GastoController($db); $c->registrar(); break;
    case 'eliminar_gasto':
        $c = new GastoController($db); $c->eliminar(); break;

    // --- USUARIOS ---
    case 'usuarios':
        $c = new UsuarioController($db); $c->index(); break;
    case 'nuevo_usuario':
        $c = new UsuarioController($db); $c->create(); break;
    case 'guardar_usuario':
        $c = new UsuarioController($db); $c->store(); break;
    case 'editar_usuario':
        $c = new UsuarioController($db); $c->edit(); break;
    case 'actualizar_usuario':
        $c = new UsuarioController($db); $c->update(); break;
    case 'eliminar_usuario':
        $c = new UsuarioController($db); $c->delete(); break;

    // --- CONFIGURACIÓN ---
    case 'configuracion':
        $c = new ConfigController($db); $c->index(); break;
    case 'guardar_talla':
        $c = new ConfigController($db); $c->store_talla(); break;
    case 'guardar_color':
        $c = new ConfigController($db); $c->store_color(); break;
    case 'borrar_talla':
        $c = new ConfigController($db); $c->delete_talla(); break;
    case 'borrar_color':
        $c = new ConfigController($db); $c->delete_color(); break;

    // --- REPORTES ---
    case 'reportes':
        $c = new ReporteController($db); $c->index(); break;

    // --- BACKUP ---
    case 'backup':
        $c = new BackupController(); $c->descargar(); break;

    case 'config_empresa':
        $c = new ConfigEmpresaController($db); $c->index(); break;
    case 'guardar_empresa':
        $c = new ConfigEmpresaController($db); $c->guardar(); break;
    case 'guardar_correlativos':
        $c = new ConfigEmpresaController($db); $c->guardarCorrelativos(); break;
    case 'subir_certificado':
        $c = new ConfigEmpresaController($db); $c->subirCertificado(); break;
    case 'enviar_sunat':
        $c = new SunatController($db);
        $c->enviar();break;

    case 'reenviar_sunat':
        $c = new SunatController($db);
        $c->reenviar();
        break;
    // --- REPARACIÓN EMERGENCIA ---
    case 'fix_caja':
        if (!isset($_SESSION['user_rol']) || $_SESSION['user_rol'] != 'ADMIN') {
            header("Location: index.php?route=login"); exit;
        }
        $db->exec("UPDATE cajas SET fecha_cierre = datetime('now','localtime'), monto_final = 0, estado = 0 WHERE estado = 1");
        session_destroy();
        echo "<script>alert('🛠️ Todas las cajas abiertas fueron cerradas. Vuelve a iniciar sesión.'); window.location.href='index.php?route=login';</script>";
        break;

    default:
        header("Location: index.php?route=login"); break;
}
?>