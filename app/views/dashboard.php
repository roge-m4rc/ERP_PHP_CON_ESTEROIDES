<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">

    <?php if(isset($_GET['cerrada']) && $_GET['cerrada']=='1'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-check-circle"></i> ✅ Caja cerrada exitosamente.
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    <?php if(isset($_GET['msg']) && $_GET['msg']=='caja_abierta'): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <i class="bi bi-unlock"></i> ✅ Caja aperturada. ¡Buenas ventas!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-speedometer2"></i> Dashboard</h2>
        <small class="text-muted"><?php echo date("d/m/Y H:i"); ?></small>
    </div>

    <!-- TARJETAS KPI -->
    <div class="row mb-4">
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-white bg-success shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-cash-coin fs-2 opacity-75"></i>
                    <div class="small mt-1">Ventas Hoy</div>
                    <h3 class="mb-0 fw-bold">S/ <?php echo number_format($ventas_hoy, 2); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-white bg-primary shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-box-seam fs-2 opacity-75"></i>
                    <div class="small mt-1">Modelos Registrados</div>
                    <h3 class="mb-0 fw-bold"><?php echo $total_productos; ?></h3>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-white bg-danger shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-exclamation-triangle fs-2 opacity-75"></i>
                    <div class="small mt-1">Stock Crítico</div>
                    <h3 class="mb-0 fw-bold"><?php echo $stock_bajo; ?></h3>
                    <?php if($stock_bajo > 0): ?>
                    <a href="index.php?route=productos" class="text-white small"><u>Ver productos</u></a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3 mb-3">
            <div class="card text-white bg-warning shadow-sm h-100">
                <div class="card-body text-center">
                    <i class="bi bi-receipt fs-2 opacity-75"></i>
                    <div class="small mt-1">Tickets Hoy</div>
                    <h3 class="mb-0 fw-bold"><?php echo $tickets_hoy; ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- ACCIONES RÁPIDAS -->
    <div class="row mb-4">
        <div class="col-md-12">
            <div class="card shadow-sm">
                <div class="card-body d-flex flex-wrap gap-2 align-items-center">
                    <span class="fw-bold me-2"><i class="bi bi-lightning-charge text-warning"></i> Acciones Rápidas:</span>
                    <a href="index.php?route=nueva_venta" class="btn btn-success btn-sm">
                        <i class="bi bi-cart-plus"></i> Nueva Venta
                    </a>
                    <a href="index.php?route=historial_ventas" class="btn btn-outline-dark btn-sm">
                        <i class="bi bi-receipt"></i> Ver Historial
                    </a>
                    <?php if(isset($_SESSION['user_rol']) && in_array($_SESSION['user_rol'],['ADMIN','Administrador'])): ?>
                    <a href="index.php?route=reportes" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-bar-chart"></i> Reportes
                    </a>
                    <a href="index.php?route=config_empresa" class="btn btn-outline-danger btn-sm">
                        <i class="bi bi-gear"></i> Config SUNAT
                    </a>
                    <?php endif; ?>
                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#modalGasto">
                        <i class="bi bi-dash-circle"></i> Registrar Gasto
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- GRÁFICO SEMANAL -->
    <div class="card shadow-lg mb-4">
        <div class="card-header bg-dark text-white d-flex justify-content-between">
            <span><i class="bi bi-graph-up"></i> Ventas — Últimos 7 días</span>
            <small class="opacity-75">S/ <?php echo number_format(array_sum(array_column($datos_grafico,'total')),2); ?> total</small>
        </div>
        <div class="card-body">
            <?php if(!empty($datos_grafico)): ?>
            <canvas id="graficoVentas" height="90"></canvas>
            <?php else: ?>
            <div class="text-center text-muted py-4"><i class="bi bi-graph-up fs-1"></i><p>No hay datos de ventas aún.</p></div>
            <?php endif; ?>
        </div>
    </div>

    <!-- VENTAS RECIENTES -->
    <?php if(!empty($ventas_recientes)): ?>
    <div class="card shadow-sm">
        <div class="card-header fw-bold"><i class="bi bi-clock-history"></i> Últimas Ventas</div>
        <div class="card-body p-0">
            <table class="table table-sm table-hover mb-0">
                <thead class="table-secondary">
                    <tr><th>#</th><th>Cliente</th><th>Método</th><th class="text-end">Total</th><th>Hora</th></tr>
                </thead>
                <tbody>
                    <?php foreach($ventas_recientes as $vr): ?>
                    <tr>
                        <td><a href="index.php?route=ver_ticket&id=<?php echo $vr['id']; ?>" target="_blank">#<?php echo $vr['id']; ?></a></td>
                        <td><?php echo htmlspecialchars($vr['cliente_nombre']); ?></td>
                        <td><span class="badge bg-secondary"><?php echo $vr['metodo_pago']; ?></span></td>
                        <td class="text-end fw-bold">S/ <?php echo number_format($vr['total'],2); ?></td>
                        <td class="text-muted"><?php echo date("H:i",strtotime($vr['fecha'])); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- MODAL GASTO -->
<div class="modal fade" id="modalGasto" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title"><i class="bi bi-dash-circle"></i> Registrar Salida de Dinero</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php?route=guardar_gasto" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Monto (S/)</label>
                        <input type="number" step="0.01" min="0.01" name="monto" class="form-control" required placeholder="0.00">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Descripción</label>
                        <textarea name="descripcion" class="form-control" required rows="2"
                            placeholder="Ej: Pago de almuerzo, taxi, compra de insumos..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-dash-circle"></i> Registrar Retiro</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php if(!empty($datos_grafico)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const labels  = [<?php foreach($datos_grafico as $d) echo '"'.date('D d/m',strtotime($d['dia'])).'",'; ?>];
const totales = [<?php foreach($datos_grafico as $d) echo floatval($d['total']).','; ?>];

new Chart(document.getElementById('graficoVentas').getContext('2d'), {
    type: 'bar',
    data: {
        labels,
        datasets: [{
            label: 'Ventas S/',
            data: totales,
            backgroundColor: totales.map(v => v === Math.max(...totales) ? 'rgba(40,167,69,0.8)' : 'rgba(54,162,235,0.6)'),
            borderColor: 'rgba(54,162,235,1)',
            borderWidth: 1,
            borderRadius: 4
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { callbacks: { label: ctx => 'S/ ' + ctx.parsed.y.toFixed(2) } }
        },
        scales: {
            y: { beginAtZero: true, ticks: { callback: v => 'S/ ' + v } }
        }
    }
});
</script>
<?php endif; ?>

<?php require_once '../app/views/includes/footer.php'; ?>
