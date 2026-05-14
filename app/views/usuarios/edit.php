<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-pencil-square"></i> Editar Usuario</h2>
        <a href="index.php?route=usuarios" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card shadow-sm" style="max-width: 600px;">
        <div class="card-body">
            <form action="index.php?route=actualizar_usuario" method="POST">
                <input type="hidden" name="id_usuario" value="<?php echo $usuario['id_usuario']; ?>">
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Nombre Completo</label>
                    <input type="text" name="nombre" class="form-control" 
                           value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Usuario (Login)</label>
                    <input type="text" name="usuario" class="form-control" 
                           value="<?php echo htmlspecialchars($usuario['usuario']); ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Nueva Contraseña</label>
                    <input type="password" name="password" class="form-control" 
                           placeholder="Dejar en blanco para mantener la actual">
                    <div class="form-text text-muted">Solo llenar si quieres cambiar la contraseña.</div>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Rol</label>
                    <select name="rol" class="form-select" required>
                        <option value="ADMIN" <?php echo ($usuario['rol'] == 'ADMIN' || $usuario['rol'] == 'Administrador') ? 'selected' : ''; ?>>ADMIN</option>
                        <option value="CAJERO" <?php echo $usuario['rol'] == 'CAJERO' ? 'selected' : ''; ?>>CAJERO</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Estado</label>
                    <select name="estado" class="form-select" required>
                        <option value="1" <?php echo $usuario['estado'] == 1 ? 'selected' : ''; ?>>Activo</option>
                        <option value="0" <?php echo $usuario['estado'] == 0 ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>
                
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Guardar Cambios
                    </button>
                    <a href="index.php?route=usuarios" class="btn btn-outline-secondary">Cancelar</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>