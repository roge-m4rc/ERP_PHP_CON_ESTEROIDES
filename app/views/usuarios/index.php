<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-people"></i> Gestión de Personal</h2>
        <a href="index.php?route=nuevo_usuario" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </a>
    </div>

    <?php if(isset($_GET['ok'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php 
            $msg = match($_GET['ok']) {
                '1' => '✅ Usuario creado correctamente.',
                '2' => '✅ Usuario actualizado correctamente.',
                '3' => '✅ Usuario eliminado correctamente.',
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
                'self' => '❌ No puedes eliminarte a ti mismo.',
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
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Usuario (Login)</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(isset($usuarios) && !empty($usuarios)): ?>
                        <?php foreach ($usuarios as $u): ?>
                        <tr>
                            <td class="fw-bold"><?php echo $u['id_usuario']; ?></td>
                            <td><?php echo htmlspecialchars($u['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($u['usuario']); ?></td>
                            <td>
                                <?php if($u['rol'] == 'ADMIN' || $u['rol'] == 'Administrador'): ?>
                                    <span class="badge bg-danger">ADMIN</span>
                                <?php else: ?>
                                    <span class="badge bg-info text-dark">CAJERO</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if($u['estado'] == 1): ?>
                                    <span class="badge bg-success">Activo</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactivo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="index.php?route=editar_usuario&id=<?php echo $u['id_usuario']; ?>" 
                                   class="btn btn-sm btn-warning" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <?php if($u['id_usuario'] != $_SESSION['user_id']): ?>
                                <a href="index.php?route=eliminar_usuario&id=<?php echo $u['id_usuario']; ?>" 
                                   class="btn btn-sm btn-danger" 
                                   onclick="return confirm('⚠️ ¿Eliminar a <?php echo htmlspecialchars($u['nombre']); ?>? Esta acción no se puede deshacer.')"
                                   title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </a>
                                <?php else: ?>
                                <button class="btn btn-sm btn-danger" disabled title="No puedes eliminarte">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="text-center py-4">No hay usuarios registrados.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>