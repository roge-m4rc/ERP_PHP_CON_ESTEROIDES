<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tags"></i> Gestión de Categorías</h2>
        <a href="index.php?route=nueva_categoria" class="btn btn-primary">
            <i class="bi bi-plus-lg"></i> Nueva Categoría
        </a>
    </div>

    <!-- Mensajes -->
    <?php if(isset($_GET['ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
            $msg = match($_GET['ok']) {
                '1' => '✅ Categoría creada correctamente.',
                '2' => '✅ Categoría actualizada correctamente.',
                '3' => '✅ Categoría eliminada correctamente.',
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
                'hasproducts' => '❌ No se puede eliminar. Hay productos usando esta categoría.',
                'notfound' => '❌ Categoría no encontrada.',
                default => '❌ Error en la operación.'
            };
            echo $msg;
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
        <div class="card-body">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre de Categoría</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($categorias)): ?>
                        <?php foreach ($categorias as $cat): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $cat['id']; ?></td>
                            <td><?php echo htmlspecialchars($cat['nombre']); ?></td>
                            <td class="text-center">
                                <a href="index.php?route=editar_categoria&id=<?php echo $cat['id']; ?>" 
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                <a href="index.php?route=eliminar_categoria&id=<?php echo $cat['id']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('¿Eliminar categoría &quot;<?php echo htmlspecialchars($cat['nombre']); ?>&quot;?')"
                                   title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="3" class="text-center py-4">No hay categorías registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>