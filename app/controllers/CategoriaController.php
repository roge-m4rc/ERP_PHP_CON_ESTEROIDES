<?php
require_once '../app/models/Categoria.php';

class CategoriaController {
    private $db;
    private $categoriaModel;

    public function __construct($db) {
        $this->db = $db;
        $this->categoriaModel = new Categoria($db);
    }

    public function index() {
        $stmt = $this->categoriaModel->listar();
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        require_once '../app/views/categorias/index.php';
    }

    public function create() {
        require_once '../app/views/categorias/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $this->categoriaModel->nombre = $_POST['nombre'];

            if($this->categoriaModel->crear()) {
                header("Location: index.php?route=categorias&ok=1");
            } else {
                header("Location: index.php?route=nueva_categoria&error=duplicate");
            }
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? 0;
        $categoria = $this->categoriaModel->getById($id);
        if (!$categoria) {
            header("Location: index.php?route=categorias&error=notfound");
            exit;
        }
        require_once '../app/views/categorias/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id = $_POST['id'] ?? 0;
            $nombre = $_POST['nombre'] ?? '';
            
            if($this->categoriaModel->actualizar($id, $nombre)) {
                header("Location: index.php?route=categorias&ok=2");
            } else {
                header("Location: index.php?route=categorias&error=1");
            }
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? 0;
        
        // Verificar si hay productos usando esta categoría
        $stmt = $this->db->prepare("SELECT COUNT(*) as total FROM productos WHERE categoria_id = ? AND estado = 1");
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['total'] > 0) {
            header("Location: index.php?route=categorias&error=hasproducts");
            exit;
        }
        
        if($this->categoriaModel->eliminar($id)) {
            header("Location: index.php?route=categorias&ok=3");
        } else {
            header("Location: index.php?route=categorias&error=1");
        }
        exit;
    }
}
?>