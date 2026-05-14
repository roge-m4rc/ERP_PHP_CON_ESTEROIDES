<?php
// Asegurar que el modelo se cargue correctamente
$modelPath = '../app/models/Producto.php';
if (!file_exists($modelPath)) {
    die("ERROR: No se encuentra el archivo $modelPath");
}
require_once $modelPath;

if (!class_exists('Producto')) {
    die("ERROR: La clase Producto no existe en $modelPath");
}

require_once '../app/models/Categoria.php';
require_once '../app/models/Variante.php';

class ProductoController {
    private $db;
    private $productoModel;
    private $categoriaModel;
    private $varianteModel;

    public function __construct($db) {
        $this->db = $db;
        $this->productoModel = new Producto($db);
        $this->categoriaModel = new Categoria($db);
        $this->varianteModel = new Variante($db);
    }

    public function index() {
        $stmt = $this->productoModel->listar();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once '../app/views/productos/index.php';
    }
    public function exportarExcel() {
        $stmt = $this->productoModel->listar();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Forzar descarga como archivo Excel (.xls)
        header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
        header('Content-Disposition: attachment; filename=productos_' . date('Ymd_His') . '.xls');
        header('Pragma: no-cache');
        header('Expires: 0');

        // BOM para UTF-8
        echo chr(0xEF) . chr(0xBB) . chr(0xBF);

        // Tabla HTML que Excel interpreta
        echo '<table border="1">';
        echo '<tr style="background-color:#4472C4;color:white;font-weight:bold;">';
        echo '<th>ID</th>';
        echo '<th>Código</th>';
        echo '<th>Nombre</th>';
        echo '<th>Categoría</th>';
        echo '<th>Precio Compra</th>';
        echo '<th>Precio Venta</th>';
        echo '<th>Stock Total</th>';
        echo '<th>Fecha Creación</th>';
        echo '</tr>';

        foreach ($productos as $p) {
            $stock_total = $this->varianteModel->getStockTotalPorProducto($p['id']);

            echo '<tr>';
            echo '<td>' . $p['id'] . '</td>';
            echo '<td>' . htmlspecialchars($p['codigo'] ?? '') . '</td>';
            echo '<td>' . htmlspecialchars($p['nombre']) . '</td>';
            echo '<td>' . htmlspecialchars($p['nombre_categoria'] ?? 'Sin categoría') . '</td>';
            echo '<td style="text-align:right;">S/ ' . number_format($p['precio_compra'] ?? 0, 2) . '</td>';
            echo '<td style="text-align:right;">S/ ' . number_format($p['precio_venta'] ?? 0, 2) . '</td>';
            echo '<td style="text-align:center;">' . $stock_total . '</td>';
            echo '<td>' . ($p['created_at'] ?? '') . '</td>';
            echo '</tr>';
        }

        echo '</table>';
        exit;
    }

    public function create() {
        $stmt = $this->categoriaModel->listar();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once '../app/views/productos/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->productoModel->codigo = $_POST['codigo'] ?? null;
            $this->productoModel->nombre = $_POST['nombre'];
            $this->productoModel->categoria_id = $_POST['categoria_id'];
            $this->productoModel->precio_compra = $_POST['precio_compra'];
            $this->productoModel->precio_venta = $_POST['precio_venta'];

            $resultado = $this->productoModel->crear();
            
            if($resultado) {
                header("Location: index.php?route=productos&ok=1");
            } else {
                header("Location: index.php?route=nuevo_producto&error=duplicate");
            }
            exit;
        }
    }
    
    public function variantes() {
        $id_producto = $_GET['id'];
        $tallas = $this->varianteModel->getTallas();
        $colores = $this->varianteModel->getColores();
        $variantes = $this->varianteModel->listarPorProducto($id_producto);
        require_once '../app/views/productos/variantes.php';
    }

    // CORRECCIÓN: Quitada asignación de 'codigo'
    public function guardar_variante() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->varianteModel->producto_id = $_POST['producto_id'];
            $this->varianteModel->talla = $_POST['talla'];
            $this->varianteModel->color = $_POST['color'];
            $this->varianteModel->stock = $_POST['stock'];

            if($this->varianteModel->crear()) {
                header("Location: index.php?route=gestionar_variantes&id=" . $_POST['producto_id']);
            } else {
                echo "Error: Esa combinación ya existe.";
            }
        }
    }

    public function eliminar_variante() {
        $id_variante = $_GET['id_var'];
        $id_producto = $_GET['id_prod'];

        if ($this->varianteModel->eliminar($id_variante)) {
            header("Location: index.php?route=gestionar_variantes&id=" . $id_producto);
        } else {
            echo "<script>alert('❌ No se puede eliminar.'); window.history.back();</script>";
        }
    }

    public function actualizar_stock_manual() {
        $id_variante = $_POST['id_variante'];
        $id_producto = $_POST['id_producto'];
        $nuevo_stock = $_POST['nuevo_stock'];

        $this->varianteModel->actualizarStock($id_variante, $nuevo_stock);
        header("Location: index.php?route=gestionar_variantes&id=" . $id_producto);
    }
    public function edit() {
        $id = $_GET['id'] ?? 0;
        $producto = $this->productoModel->getById($id);
        
        if (!$producto) {
            header("Location: index.php?route=productos&error=notfound");
            exit;
        }
        
        $stmt = $this->categoriaModel->listar();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        require_once '../app/views/productos/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? 0;
            
            $this->productoModel->codigo = $_POST['codigo'] ?? null;
            $this->productoModel->nombre = $_POST['nombre'];
            $this->productoModel->categoria_id = $_POST['categoria_id'];
            $this->productoModel->precio_compra = $_POST['precio_compra'];
            $this->productoModel->precio_venta = $_POST['precio_venta'];

            if($this->productoModel->actualizar($id)) {
                header("Location: index.php?route=productos&ok=2");
            } else {
                header("Location: index.php?route=editar_producto&id=" . $id . "&error=duplicate");
            }
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        if($this->productoModel->eliminar($id)) {
            header("Location: index.php?route=productos&ok=3");
        } else {
            header("Location: index.php?route=productos&error=1");
        }
        exit;
    }
}
?>