<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <h2 class="mb-4">Registrar Nuevo Empleado</h2>

    <div class="card shadow-sm" style="max-width: 500px;">
        <div class="card-body">
            <form action="index.php?route=guardar_usuario" method="POST">
                
                <div class="mb-3">
                    <label class="form-label">Nombre Completo</label>
                    <input type="text" name="nombre" class="form-control" placeholder="Ej: Juan Perez" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Usuario (para Login)</label>
                    <input type="text" name="usuario" class="form-control" placeholder="Ej: cajero1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Contraseña</label>
                    <input type="password" name="password" class="form-control" required>
                    <div class="form-text">El sistema la protegerá automáticamente.</div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Rol / Permisos</label>
                    <select name="rol" class="form-select">
                        <option value="CAJERO">Cajero (Solo Vende)</option>
                        <option value="ADMIN">Administrador (Control Total)</option>
                    </select>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                    <a href="index.php?route=usuarios" class="btn btn-secondary">Cancelar</a>
                </div>

            </form>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>