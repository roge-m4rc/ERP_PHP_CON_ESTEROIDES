<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-receipt"></i> Historial de Ventas</h2>
        <a href="index.php?route=exportar_historial<?php echo !empty($_GET['f_ini']) ? '&f_ini=' . $_GET['f_ini'] : ''; ?><?php echo !empty($_GET['f_fin']) ? '&f_fin=' . $_GET['f_fin'] : ''; ?>" class="btn btn-success">
            <i class="bi bi-file-earmark-excel"></i> Exportar Excel
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card shadow-sm mb-3">
        <div class="card-body bg-light py-2">
            <form action="index.php" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="route" value="historial_ventas">
                <div class="col-md-3">
                    <label class="form-label small fw-bold mb-1">Desde:</label>
                    <input type="date" name="f_ini" class="form-control form-control-sm" value="<?php echo $_GET['f_ini'] ?? ''; ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-bold mb-1">Hasta:</label>
                    <input type="date" name="f_fin" class="form-control form-control-sm" value="<?php echo $_GET['f_fin'] ?? ''; ?>">
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary btn-sm w-100">Filtrar</button>
                </div>
                <div class="col-md-2">
                    <a href="index.php?route=historial_ventas" class="btn btn-outline-secondary btn-sm w-100">Ver Todo</a>
                </div>
            </form>
        </div>
    </div>

    <?php if(isset($_GET['msg']) && $_GET['msg']=='anulado'): ?>
    <div class="alert alert-success alert-dismissible fade show">✅ Venta anulada y stock devuelto.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <!-- TABLA -->
    <div class="card shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th># Ticket</th>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Método</th>
                        <th class="text-end">IGV</th>
                        <th class="text-end">Total</th>
                        <th>Estado</th>
                        <th>SUNAT</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $total_mostrado = 0;
                    foreach ($ventas as $v): 
                        $anulado = ($v['estado'] == 0);
                        if(!$anulado) $total_mostrado += $v['total'];
                    ?>
                    <tr class="<?php echo $anulado ? 'table-danger' : ''; ?>">
                        <td class="fw-bold">
                            <?php echo str_pad($v['id'], 5, "0", STR_PAD_LEFT); ?><br>
                            <small class="text-muted"><?php echo $v['serie'] . '-' . $v['correlativo']; ?></small>
                        </td>
                        <td><?php echo date("d/m/Y H:i", strtotime($v['fecha'])); ?></td>
                        <td>
                            <?php echo htmlspecialchars($v['cliente_nombre'] ?? 'General'); ?><br>
                            <small class="text-muted"><?php echo $v['cliente_tipo_doc'] . ': ' . $v['cliente_num_doc']; ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($v['vendedor']); ?></td>
                        <td>
                            <?php 
                            $colores = ['EFECTIVO'=>'success','YAPE'=>'info','TARJETA'=>'warning'];
                            $col = $colores[$v['metodo_pago']] ?? 'secondary';
                            ?>
                            <span class="badge bg-<?php echo $col; ?>"><?php echo $v['metodo_pago']; ?></span>
                        </td>
                        <td class="text-end small">
                            S/ <?php echo number_format($v['igv'] ?? 0, 2); ?>
                        </td>
                        <td class="text-end fw-bold <?php echo $anulado ? 'text-decoration-line-through text-muted' : ''; ?>">
                            S/ <?php echo number_format($v['total'], 2); ?>
                        </td>
                        <td>
                            <?php if($anulado): ?>
                                <span class="badge bg-danger">ANULADO</span>
                            <?php else: ?>
                                <span class="badge bg-success">ACTIVO</span>
                            <?php endif; ?>
                        </td>
                        
                        <!-- COLUMNA SUNAT -->
                        <td>
                            <?php 
                            $estado_sunat = $v['estado_sunat'] ?? 'REGISTRADO';
                            ?>
                            <?php if ($estado_sunat == 'ACEPTADO'): ?>
                                <span class="badge bg-success"><i class="bi bi-check-circle"></i> Aceptado</span>
                            <?php elseif ($estado_sunat == 'RECHAZADO'): ?>
                                <span class="badge bg-danger"><i class="bi bi-x-circle"></i> Rechazado</span>
                                <a href="index.php?route=reenviar_sunat&id=<?= $v['id'] ?>" class="btn btn-sm btn-warning mt-1 d-block" title="Reintentar envío a SUNAT">
                                    🔄 Reenviar
                                </a>
                            <?php elseif ($estado_sunat == 'ENVIADO'): ?>
                                <span class="badge bg-info"><i class="bi bi-send"></i> Enviado</span>
                            <?php else: ?>
                                <span class="badge bg-secondary"><i class="bi bi-clock"></i> Registrado</span>
                                <a href="index.php?route=enviar_sunat&id=<?= $v['id'] ?>" class="btn btn-sm btn-primary mt-1 d-block">
                                    🚀 Enviar a SUNAT
                                </a>
                            <?php endif; ?>
                        </td>
                        
                        <td>
                            <a href="index.php?route=ver_ticket&id=<?php echo $v['id']; ?>" 
                               class="btn btn-sm btn-outline-dark" target="_blank" title="Ver ticket">
                                <i class="bi bi-printer"></i>
                            </a>
                            <?php if(!$anulado && ($_SESSION['user_rol'] == 'ADMIN')): ?>
                            <a href="index.php?route=anular_venta&id=<?php echo $v['id']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('⚠️ ¿Anular venta #<?php echo $v['id']; ?>? El stock será devuelto.')">
                               <i class="bi bi-x-circle"></i>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <?php if(!empty($ventas)): ?>
                <tfoot class="table-dark">
                    <tr>
                        <td colspan="6" class="text-end fw-bold">TOTAL ACTIVAS:</td>
                        <td class="text-end fw-bold">S/ <?php echo number_format($total_mostrado, 2); ?></td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
            </div>
        </div>
    </div>
    <?php if(empty($ventas)): ?>
    <div class="text-center text-muted mt-4"><i class="bi bi-inbox fs-1"></i><p>No hay ventas registradas.</p></div>
    <?php endif; ?>
</div>

<?php require_once '../app/views/includes/footer.php'; ?>