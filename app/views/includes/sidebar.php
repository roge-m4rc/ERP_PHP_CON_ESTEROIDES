<div class="sidebar p-3">
    <h5 class="mb-4 text-white"><?php echo htmlspecialchars($cfgEmpresa['nombre_empresa'] ?? 'Menú Principal'); ?></h5>
    <?php 
    $r = isset($_GET['route']) ? $_GET['route'] : '';
    $active = function($route) use ($r) { return ($r == $route) ? 'active' : ''; };
    ?>
    <ul class="list-unstyled">
        <li class="mb-2">
            <a href="index.php?route=dashboard" class="rounded <?php echo $active('dashboard'); ?>">
                <i class="bi bi-speedometer2 me-2"></i> Inicio
            </a>
        </li>

        <li class="mb-1 mt-3"><small class="text-uppercase text-white-50">Inventario</small></li>
        <li class="mb-1"><a href="index.php?route=categorias" class="<?php echo $active('categorias'); ?>">
            <i class="bi bi-tags me-2"></i> Categorías</a></li>
        <li class="mb-1"><a href="index.php?route=productos" class="<?php echo $active('productos'); ?>">
            <i class="bi bi-box-seam me-2"></i> Productos</a></li>

        <li class="mb-1 mt-3"><small class="text-uppercase text-white-50">Ventas</small></li>
        <li class="mb-1"><a href="index.php?route=nueva_venta" class="<?php echo $active('nueva_venta'); ?>">
            <i class="bi bi-cart-plus me-2"></i> Nueva Venta</a></li>
        <li class="mb-1"><a href="index.php?route=historial_ventas" class="<?php echo $active('historial_ventas'); ?>">
            <i class="bi bi-receipt me-2"></i> Historial</a></li>

        <li class="mb-1 mt-3"><small class="text-uppercase text-white-50">Configuración</small></li>
        <li class="mb-1"><a href="index.php?route=configuracion" class="<?php echo $active('configuracion'); ?>">
            <i class="bi bi-gear me-2"></i> Tallas y Colores</a></li>
        <li class="mb-1"><a href="index.php?route=usuarios" class="<?php echo $active('usuarios'); ?>">
            <i class="bi bi-people me-2"></i> Usuarios</a></li>

        <?php if(isset($_SESSION['user_rol']) && in_array($_SESSION['user_rol'],['ADMIN','Administrador'])): ?>
        <li class="mb-1 mt-3"><small class="text-uppercase text-white-50">Administración</small></li>
        <li class="mb-1"><a href="index.php?route=reportes" class="<?php echo $active('reportes'); ?>">
            <i class="bi bi-bar-chart me-2"></i> Reportes</a></li>
        <?php endif; ?>

        <li class="mb-1 mt-3"><small class="text-uppercase text-white-50">Caja</small></li>
        <li class="mb-1"><a href="index.php?route=caja_apertura" class="<?php echo $active('caja_apertura'); ?>">
            <i class="bi bi-unlock me-2 text-success"></i> Apertura</a></li>
        <li class="mb-1"><a href="index.php?route=caja_cierre" class="text-warning <?php echo $active('caja_cierre'); ?>">
            <i class="bi bi-wallet2 me-2"></i> Cerrar Caja</a></li>

        <li class="mt-4 pt-2 border-top">
            <a href="index.php?route=logout" class="text-danger">
                <i class="bi bi-box-arrow-left me-2"></i> Cerrar Sesión
            </a>
        </li>
        <li class="mt-2">
            <a href="index.php?route=backup" class="text-info">
                <i class="bi bi-cloud-download me-2"></i> Backup BD
            </a>
        </li>
    </ul>
    <div class="mt-4 pt-3 border-top text-center">
        <small class="text-white-50">v2.0 — <?php echo date("d/m/Y"); ?></small>
    </div>
</div>
