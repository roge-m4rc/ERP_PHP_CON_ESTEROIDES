<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <div class="mb-4">
        <a href="index.php?route=productos" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-left"></i> Volver al Catálogo</a>
        <h2 class="mt-2">Gestionar Stock y Variantes</h2>
        <p class="text-muted">Agrega tallas y colores para este producto.</p>
    </div>

    <div class="row">
        <div class="col-md-4">
            <div class="card shadow-sm border-primary">
                <div class="card-header bg-primary text-white">Agregar Variante</div>
                <div class="card-body">
                    <form action="index.php?route=guardar_variante" method="POST">
                        <input type="hidden" name="producto_id" value="<?php echo $id_producto; ?>">

                        <div class="mb-3">
                            <label class="form-label">Talla</label>
                            <input type="text" name="talla" class="form-control" placeholder="Ej: M, L, XL, 42" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Color</label>
                            <input type="text" name="color" class="form-control" placeholder="Ej: Negro, Azul, Rojo" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Cantidad Inicial (Stock)</label>
                            <input type="number" name="stock" class="form-control" value="1" min="1" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-plus-circle"></i> Agregar al Inventario
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">Inventario Actual</h5>
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Talla</th>
                                <th>Color</th>
                                <th>Stock</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($variantes as $v): ?>
                            <tr>
                                <td><small><?php echo $v['id']; ?></small></td>
                                <td><strong><?php echo $v['talla']; ?></strong></td>
                                <td><?php echo $v['color']; ?></td>
                                
                                <td>
                                    <form action="index.php?route=actualizar_stock_manual" method="POST" class="d-flex">
                                        <input type="hidden" name="id_variante" value="<?php echo $v['id']; ?>">
                                        <input type="hidden" name="id_producto" value="<?php echo $id_producto; ?>">
                                        
                                        <input type="number" name="nuevo_stock" value="<?php echo $v['stock']; ?>" 
                                               class="form-control form-control-sm me-1" style="width: 70px;">
                                        
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Actualizar">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                </td>
                                
                                <td>
                                    <a href="index.php?route=eliminar_variante&id_var=<?php echo $v['id']; ?>&id_prod=<?php echo $id_producto; ?>" 
                                       class="btn btn-sm btn-outline-danger" 
                                       onclick="return confirm('¿Eliminar esta variante?');">
                                       <i class="bi bi-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>