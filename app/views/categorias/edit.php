<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-pencil-square"></i> Editar Categoría</h2>
        <a href="index.php?route=categorias" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
    
    <div class="card shadow-sm" style="max-width: 500px;">
        <div class="card-body">
            <form action="index.php?route=actualizar_categoria" method="POST">
                <input type="hidden" name="id" value="<?php echo $categoria['id']; ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre de la Categoría</label>
                    <input type="text" name="nombre" class="form-control" 
                           value="<?php echo htmlspecialchars($categoria['nombre']); ?>" required>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Guardar Cambios
                    </button>
                    <a href="index.php?route=categorias" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>