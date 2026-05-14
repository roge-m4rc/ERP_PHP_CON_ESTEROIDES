<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<style>
    #print-header { display:none; }
    @media print {
        .sidebar,.btn,form,.badge,.no-print { display:none !important; }
        .content { margin:0 !important; padding:10px !important; width:100% !important; }
        #print-header { display:block; text-align:center; margin-bottom:15px; border-bottom:2px solid #000; padding-bottom:10px; }
        .card { border:1px solid #ccc !important; break-inside:avoid; margin-bottom:15px; }
        .card-header { background:#eee !important; color:#000 !important; }
    }
</style>

<div class="content">

    <!-- ENCABEZADO IMPRESIÓN -->
    <div id="print-header">
        <h2><?php echo htmlspecialchars($config['nombre_empresa'] ?? "MACHO'S BOUTIQUE"); ?></h2>
        <?php if(!empty($config['ruc'])): ?><p>RUC: <?php echo $config['ruc']; ?></p><?php endif; ?>
        <p>Reporte Financiero — Del <?php echo date("d/m/Y", strtotime($fecha_inicio)); ?> al <?php echo date("d/m/Y", strtotime($fecha_fin)); ?></p>
        <small>Generado: <?php echo date("d/m/Y H:i"); ?></small>
    </div>

    <!-- ENCABEZADO PANTALLA -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <h2><i class="bi bi-bar-chart-line"></i> Reporte Financiero</h2>
        <button onclick="window.print()" class="btn btn-danger">
            <i class="bi bi-file-earmark-pdf"></i> Imprimir / PDF
        </button>
    </div>

    <!-- FILTRO FECHAS -->
    <div class="card shadow-sm mb-4 border-primary no-print">
        <div class="card-body bg-light">
            <form action="index.php" method="GET" class="row g-2 align-items-end">
                <input type="hidden" name="route" value="reportes">
                <div class="col-md-4">
                    <label class="form-label fw-bold">Desde:</label>
                    <input type="date" name="f_inicio" class="form-control" value="<?php echo $fecha_inicio; ?>" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Hasta:</label>
                    <input type="date" name="f_fin" class="form-control" value="<?php echo $fecha_fin; ?>" required>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filtrar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- RESUMEN VENTAS -->
    <h5 class="mb-3 text-secondary"><i class="bi bi-cash-coin"></i> Resumen del Período</h5>
    <div class="row mb-4">
        <div class="col-6 col-md-3">
            <div class="card text-white bg-dark mb-3 text-center">
                <div class="card-body py-3">
                    <div class="small">Total Vendido</div>
                    <h3 class="mb-0">S/ <?php echo number_format($total_general, 2); ?></h3>
                    <small class="opacity-75"><?php echo $cantidad_ventas; ?> tickets</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-white bg-success mb-3 text-center">
                <div class="card-body py-3">
                    <div class="small">Efectivo</div>
                    <h3 class="mb-0">S/ <?php echo number_format($total_efectivo, 2); ?></h3>
                    <small class="opacity-75"><?php echo $total_general > 0 ? round($total_efectivo/$total_general*100) : 0; ?>%</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-dark bg-info mb-3 text-center">
                <div class="card-body py-3">
                    <div class="small">Yape / Plin</div>
                    <h3 class="mb-0">S/ <?php echo number_format($total_yape, 2); ?></h3>
                    <small><?php echo $total_general > 0 ? round($total_yape/$total_general*100) : 0; ?>%</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card text-dark bg-warning mb-3 text-center">
                <div class="card-body py-3">
                    <div class="small">Tarjetas</div>
                    <h3 class="mb-0">S/ <?php echo number_format($total_tarjeta, 2); ?></h3>
                    <small><?php echo $total_general > 0 ? round($total_tarjeta/$total_general*100) : 0; ?>%</small>
                </div>
            </div>
        </div>
    </div>

    <!-- FILA: GASTOS + NETO -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card border-danger text-center">
                <div class="card-body py-3">
                    <div class="text-danger fw-bold">Total Gastos del Período</div>
                    <h4 class="text-danger">- S/ <?php echo number_format($total_gastos_periodo, 2); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <?php $neto = $total_general - $total_gastos_periodo; ?>
            <div class="card border-<?php echo $neto >= 0 ? 'success' : 'danger'; ?> text-center">
                <div class="card-body py-3">
                    <div class="fw-bold text-<?php echo $neto >= 0 ? 'success' : 'danger'; ?>">Neto (Ventas - Gastos)</div>
                    <h4 class="text-<?php echo $neto >= 0 ? 'success' : 'danger'; ?>">S/ <?php echo number_format($neto, 2); ?></h4>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-secondary text-center">
                <div class="card-body py-3">
                    <div class="fw-bold text-secondary">Ticket Promedio</div>
                    <h4><?php echo $cantidad_ventas > 0 ? 'S/ ' . number_format($total_general/$cantidad_ventas, 2) : 'S/ 0.00'; ?></h4>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICO VENTAS POR DÍA -->
    <?php if(!empty($datos_grafico)): ?>
    <div class="card shadow-sm mb-4 no-print">
        <div class="card-header bg-dark text-white fw-bold">
            <i class="bi bi-graph-up"></i> Ventas por Día
        </div>
        <div class="card-body">
            <canvas id="graficoPeriodo" height="80"></canvas>
        </div>
    </div>
    <?php endif; ?>

    <!-- TOP 5 PRODUCTOS -->
    <?php if(!empty($top_productos)): ?>
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white fw-bold">
            <i class="bi bi-trophy"></i> Top 5 Productos Más Vendidos
        </div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-light">
                    <tr><th>#</th><th>Producto</th><th class="text-end">Und. Vendidas</th><th class="text-end">Monto Total</th></tr>
                </thead>
                <tbody>
                    <?php foreach($top_productos as $i => $p): ?>
                    <tr>
                        <td><span class="badge bg-primary"><?php echo $i+1; ?></span></td>
                        <td><?php echo htmlspecialchars($p['nombre']); ?></td>
                        <td class="text-end fw-bold"><?php echo $p['total_vendido']; ?> und.</td>
                        <td class="text-end">S/ <?php echo number_format($p['monto_total'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- HISTORIAL DE CAJAS / TURNOS -->
    <div class="card shadow-sm mb-4 border-warning">
        <div class="card-header bg-warning text-dark fw-bold">
            <i class="bi bi-person-badge"></i> Historial de Turnos / Cajas
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-striped table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Cajero</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Estado</th>
                        <th class="text-end">Inicial</th>
                        <th class="text-end">Ventas Turno</th>
                        <th class="text-end">Gastos Turno</th>
                        <th class="text-end">Tickets</th>
                        <th class="text-end">Monto Final</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(!empty($historial_cajas)): ?>
                        <?php foreach($historial_cajas as $c): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($c['cajero']); ?></td>
                            <td><?php echo date("d/m H:i", strtotime($c['fecha_apertura'])); ?></td>
                            <td><?php echo $c['fecha_cierre'] ? date("d/m H:i", strtotime($c['fecha_cierre'])) : '<span class="text-success">Abierta</span>'; ?></td>
                            <td>
                                <?php if($c['estado'] == 1): ?>
                                    <span class="badge bg-success blink">ACTIVO</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">CERRADO</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">S/ <?php echo number_format($c['monto_inicial'], 2); ?></td>
                            <td class="text-end fw-bold text-success">+ S/ <?php echo number_format($c['ventas_turno'], 2); ?></td>
                            <td class="text-end text-danger">- S/ <?php echo number_format($c['gastos_turno'], 2); ?></td>
                            <td class="text-end"><?php echo $c['num_tickets']; ?></td>
                            <td class="text-end fw-bold">
                                <?php 
                                    if($c['estado'] == 0 && $c['monto_final'] !== null) {
                                        echo 'S/ ' . number_format($c['monto_final'], 2);
                                    } else {
                                        // Calcular estimado en tiempo real
                                        $est = $c['monto_inicial'] + $c['ventas_turno'] - $c['gastos_turno'];
                                        echo '<span class="text-muted">~S/ ' . number_format($est, 2) . '</span>';
                                    }
                                ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="9" class="text-center text-muted py-3">No hay cajas registradas en este período.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <!-- GASTOS DEL PERÍODO -->
    <?php if(!empty($lista_gastos)): ?>
    <div class="card shadow-sm mb-4 border-danger">
        <div class="card-header bg-danger text-white fw-bold">
            <i class="bi bi-dash-circle"></i> Gastos del Período — Total: S/ <?php echo number_format($total_gastos_periodo, 2); ?>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr><th>Fecha</th><th>Descripción</th><th>Registrado por</th><th class="text-end">Monto</th></tr>
                </thead>
                <tbody>
                    <?php foreach($lista_gastos as $g): ?>
                    <tr>
                        <td><?php echo date("d/m H:i", strtotime($g['fecha'])); ?></td>
                        <td><?php echo htmlspecialchars($g['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($g['usuario_nombre']); ?></td>
                        <td class="text-end text-danger fw-bold">S/ <?php echo number_format($g['monto'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

    <!-- DETALLE DE TICKETS -->
    <div class="card shadow-sm">
        <div class="card-header fw-bold"><i class="bi bi-receipt"></i> Detalle de Tickets Vendidos (<?php echo $cantidad_ventas; ?>)</div>
        <div class="card-body p-0">
            <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-secondary">
                    <tr><th>ID</th><th>Fecha/Hora</th><th>Cliente</th><th>Vendedor</th><th>Método</th><th class="text-end">Descuento</th><th class="text-end">Total</th></tr>
                </thead>
                <tbody>
                    <?php if(!empty($ventas)): ?>
                        <?php foreach($ventas as $v): ?>
                        <tr>
                            <td><a href="index.php?route=ver_ticket&id=<?php echo $v['id']; ?>" target="_blank">#<?php echo $v['id']; ?></a></td>
                            <td><?php echo date("d/m H:i", strtotime($v['fecha'])); ?></td>
                            <td><?php echo htmlspecialchars($v['cliente_nombre']); ?></td>
                            <td><?php echo htmlspecialchars($v['vendedor']); ?></td>
                            <td>
                                <?php 
                                $colores = ['EFECTIVO'=>'success','YAPE'=>'info','TARJETA'=>'warning'];
                                $col = $colores[$v['metodo_pago']] ?? 'secondary';
                                ?>
                                <span class="badge bg-<?php echo $col; ?>"><?php echo $v['metodo_pago']; ?></span>
                            </td>
                            <td class="text-end text-danger"><?php echo ($v['descuento']??0) > 0 ? '- S/ '.number_format($v['descuento'],2) : '-'; ?></td>
                            <td class="text-end fw-bold">S/ <?php echo number_format($v['total'], 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <tr class="table-dark fw-bold">
                            <td colspan="6" class="text-end">TOTAL PERÍODO:</td>
                            <td class="text-end">S/ <?php echo number_format($total_general, 2); ?></td>
                        </tr>
                    <?php else: ?>
                        <tr><td colspan="7" class="text-center text-muted py-3">No hay ventas en este período.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <div class="text-center mt-4 text-muted small">
        <p>__________________________<br>Visto Bueno Administrador</p>
    </div>
</div>

<?php if(!empty($datos_grafico)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels = [<?php foreach($datos_grafico as $d) echo '"'.date('d/m',strtotime($d['dia'])).'",' ?>];
const totales = [<?php foreach($datos_grafico as $d) echo $d['total'].', '; ?>];
const cantidades = [<?php foreach($datos_grafico as $d) echo $d['cantidad'].', '; ?>];

new Chart(document.getElementById('graficoPeriodo').getContext('2d'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Ventas S/',
            data: totales,
            backgroundColor: 'rgba(54,162,235,0.6)',
            borderColor: 'rgba(54,162,235,1)',
            borderWidth: 1,
            yAxisID: 'y'
        }, {
            label: 'N° Tickets',
            data: cantidades,
            type: 'line',
            borderColor: 'rgba(255,99,132,1)',
            backgroundColor: 'rgba(255,99,132,0.1)',
            borderWidth: 2,
            pointRadius: 4,
            yAxisID: 'y1'
        }]
    },
    options: {
        responsive: true,
        interaction: { mode: 'index', intersect: false },
        scales: {
            y:  { beginAtZero:true, position:'left',  title:{display:true,text:'Soles (S/)'} },
            y1: { beginAtZero:true, position:'right', title:{display:true,text:'N° Tickets'}, grid:{drawOnChartArea:false} }
        }
    }
});
</script>
<?php endif; ?>

<?php require_once '../app/views/includes/footer.php'; ?>
