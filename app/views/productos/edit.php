<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-pencil-square"></i> Editar Producto</h2>
        <a href="index.php?route=productos" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm" style="max-width: 600px;">
        <div class="card-body">
            <form action="index.php?route=actualizar_producto" method="POST">
                <input type="hidden" name="id" value="<?php echo $producto['id']; ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Código</label>
                    <input type="text" name="codigo" class="form-control" 
                           value="<?php echo htmlspecialchars($producto['codigo'] ?? ''); ?>"
                           placeholder="Dejar vacío para generar automático">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre</label>
                    <input type="text" name="nombre" class="form-control" 
                           value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Categoría</label>
                    <select name="categoria_id" class="form-select" required>
                        <?php foreach($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>" <?php echo ($cat['id'] == $producto['categoria_id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Precio Compra</label>
                        <input type="number" step="0.01" name="precio_compra" class="form-control" 
                               value="<?php echo $producto['precio_compra']; ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Precio Venta</label>
                        <input type="number" step="0.01" name="precio_venta" class="form-control" 
                               value="<?php echo $producto['precio_venta']; ?>" required>
                    </div>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Guardar Cambios
                    </button>
                    <a href="index.php?route=productos" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>