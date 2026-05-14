<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <h2 class="mb-4">Nueva Categoría</h2>

    <!-- ✅ MENSAJE DE ERROR -->
    <?php if(isset($_GET['error']) && $_GET['error'] == 'duplicate'): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            ❌ Ya existe una categoría con ese nombre. Use uno diferente.
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm" style="max-width: 500px;">
        <div class="card-body">
            <form action="index.php?route=guardar_categoria" method="POST">
                <div class="mb-3">
                    <label for="nombre" class="form-label">Nombre de la Categoría</label>
                    <input type="text" class="form-control" name="nombre" placeholder="Ej: Pantalones" required autofocus>
                </div>
                
                <div class="d-flex justify-content-between">
                    <a href="index.php?route=categorias" class="btn btn-secondary">Cancelar</a>
                    <button type="submit" class="btn btn-success">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>