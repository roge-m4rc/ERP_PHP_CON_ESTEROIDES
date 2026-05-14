<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-box-seam"></i> Catálogo de Productos</h2>
        <div class="d-flex gap-2">
            <a href="index.php?route=nuevo_producto" class="btn btn-primary">
                <i class="bi bi-plus-lg"></i> Nuevo Producto
            </a>
            <a href="index.php?route=exportar_productos" class="btn btn-success">
                <i class="bi bi-file-earmark-excel"></i> Exportar Excel
            </a>
        </div>
    </div>

    <!-- Mensajes -->
    <?php if(isset($_GET['ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
            $msg = match($_GET['ok']) {
                '1' => '✅ Producto creado correctamente.',
                '2' => '✅ Producto actualizado correctamente.',
                '3' => '✅ Producto eliminado correctamente.',
                default => '✅ Operación exitosa.'
            };
            echo $msg;
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if(isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php 
            $msg = match($_GET['error']) {
                'notfound' => '❌ Producto no encontrado.',
                default => '❌ Error en la operación.'
            };
            echo $msg;
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Cód.</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-end">Precio Venta</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($productos)): ?>
                        <?php foreach ($productos as $prod): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $prod['id']; ?></td>
                            <td><small class="text-muted"><?php echo htmlspecialchars($prod['codigo'] ?? ''); ?></small></td>
                            <td><?php echo htmlspecialchars($prod['nombre']); ?></td>
                            <td><span class="badge bg-info text-dark"><?php echo htmlspecialchars($prod['nombre_categoria'] ?? 'Sin cat.'); ?></span></td>
                            <td class="text-end fw-bold">S/ <?php echo number_format($prod['precio_venta'], 2); ?></td>
                            <td class="text-center">
                                <a href="index.php?route=editar_producto&id=<?php echo $prod['id']; ?>" 
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="index.php?route=eliminar_producto&id=<?php echo $prod['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('¿Eliminar producto &quot;<?php echo htmlspecialchars($prod['nombre']); ?>&quot;?')"
                                   title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <a href="index.php?route=gestionar_variantes&id=<?php echo $prod['id']; ?>" 
                                   class="btn btn-sm btn-outline-success" title="Variantes">
                                    <i class="bi bi-box-seam"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4">No hay productos registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>