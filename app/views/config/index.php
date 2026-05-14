<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <h2 class="mb-4"><i class="bi bi-gear"></i> Configuración de Atributos</h2>
    
    <div class="alert alert-info">
        <i class="bi bi-info-circle"></i> 
        <strong>Nota:</strong> Las tallas y colores se gestionan automáticamente al crear variantes de productos. 
        No es necesario crearlos manualmente.
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow-sm border-info">
                <div class="card-header bg-info text-dark fw-bold">
                    <i class="bi bi-rulers"></i> Tallas Existentes
                </div>
                <div class="card-body">
                    <?php if(!empty($tallas)): ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach($tallas as $t): ?>
                                <span class="badge bg-secondary fs-6"><?php echo $t['nombre']; ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay tallas registradas. Crea una variante de producto para agregar tallas.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm border-warning">
                <div class="card-header bg-warning text-dark fw-bold">
                    <i class="bi bi-palette"></i> Colores Existentes
                </div>
                <div class="card-body">
                    <?php if(!empty($colores)): ?>
                        <div class="d-flex flex-wrap gap-2">
                            <?php foreach($colores as $c): ?>
                                <span class="badge bg-secondary fs-6"><?php echo $c['nombre']; ?></span>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">No hay colores registrados. Crea una variante de producto para agregar colores.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    
    <div class="mt-4 text-center">
        <a href="index.php?route=productos" class="btn btn-primary btn-lg">
            <i class="bi bi-box-seam"></i> Ir a Productos para Crear Variantes
        </a>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>