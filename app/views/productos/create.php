<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <h2 class="mb-4">Registrar Nuevo Modelo</h2>

    <!-- ✅ MENSAJE DE ERROR -->
    <?php if(isset($_GET['error']) && $_GET['error'] == 'duplicate'): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            ❌ Ya existe un producto con ese nombre. Use uno diferente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <form action="index.php?route=guardar_producto" method="POST">
                <div class="row">
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label class="form-label">Nombre del Producto</label>
                            <input type="text" class="form-control" name="nombre" placeholder="Ej: Camisa Leñadora" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Código de Barras (Opcional)</label>
                            <input type="text" class="form-control" name="codigo" placeholder="Escanear o escribir...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Categoría</label>
                            <select class="form-select" name="categoria_id" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($categorias as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>">
                                        <?php echo $cat['nombre']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio Compra (Costo)</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" class="form-control" name="precio_compra" required>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Precio Venta (Público)</label>
                                <div class="input-group">
                                    <span class="input-group-text">S/</span>
                                    <input type="number" step="0.01" class="form-control" name="precio_venta" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-3">
                    <a href="index.php?route=productos" class="btn btn-secondary me-2">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar Producto Base</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>