<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<div class="content">
    <div class="row justify-content-center">
        <div class="col-md-7">
            <div class="card shadow-lg border-danger">
                <div class="card-header bg-danger text-white text-center">
                    <h3><i class="bi bi-lock-fill"></i> Cierre de Caja</h3>
                    <small>Cajero: <strong><?php echo $_SESSION['user_nombre']; ?></strong> — 
                    Apertura: <?php echo date("d/m/Y H:i", strtotime($caja['fecha_apertura'])); ?></small>
                </div>
                <div class="card-body">

                    <!-- RESUMEN DEL SISTEMA -->
                    <div class="alert alert-secondary mb-3">
                        <h6 class="alert-heading text-center fw-bold mb-3">📊 Resumen del Turno</h6>
                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                            <span>💰 Monto Inicial:</span>
                            <strong>S/ <?php echo number_format($caja['monto_inicial'], 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1 text-success">
                            <span>+ Ventas Efectivo:</span>
                            <strong>S/ <?php echo number_format($totales['venta_efectivo'], 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1 text-info">
                            <span>+ Ventas Yape/Plin:</span>
                            <strong>S/ <?php echo number_format($totales['venta_yape'], 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1 text-warning">
                            <span>+ Ventas Tarjeta:</span>
                            <strong>S/ <?php echo number_format($totales['venta_tarjeta'], 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1 text-danger">
                            <span>− Gastos del Turno:</span>
                            <strong>S/ <?php echo number_format($total_gastos, 2); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                            <span>🧾 Total Tickets: </span>
                            <strong><?php echo $totales['cantidad_tickets']; ?> ventas</strong>
                        </div>
                        <div class="d-flex justify-content-between border-bottom pb-1 mb-1">
                            <span>📦 Total Vendido (todos los métodos):</span>
                            <strong>S/ <?php echo number_format($totales['venta_total'], 2); ?></strong>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-5 fw-bold">
                            <span>💵 DEBERÍA HABER EN CAJÓN:</span>
                            <span class="text-primary">S/ <?php echo number_format($total_esperado_en_cajon, 2); ?></span>
                        </div>
                        <div class="text-muted small text-end">(Inicial + Efectivo − Gastos)</div>
                    </div>

                    <!-- LISTA DE GASTOS -->
                    <?php if(!empty($lista_gastos)): ?>
                    <div class="mb-3">
                        <label class="fw-bold text-danger"><i class="bi bi-dash-circle"></i> Gastos del Turno:</label>
                        <ul class="list-group list-group-flush small mt-1">
                            <?php foreach($lista_gastos as $g): ?>
                            <li class="list-group-item d-flex justify-content-between px-0">
                                <span><?php echo htmlspecialchars($g['descripcion']); ?>
                                    <small class="text-muted">(<?php echo date("H:i", strtotime($g['fecha'])); ?>)</small>
                                </span>
                                <span class="d-flex align-items-center gap-2">
                                    <span class="text-danger fw-bold">− S/ <?php echo number_format($g['monto'], 2); ?></span>
                                    <a href="index.php?route=eliminar_gasto&id=<?php echo $g['id']; ?>" 
                                       onclick="return confirm('¿Eliminar este gasto?')"
                                       class="btn btn-sm btn-outline-danger py-0 px-1">×</a>
                                </span>
                            </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>

                    <!-- AGREGAR GASTO RÁPIDO -->
                    <div class="card border-warning mb-3">
                        <div class="card-header bg-warning text-dark small fw-bold py-1">
                            <i class="bi bi-plus-circle"></i> Registrar Gasto de Último Momento
                        </div>
                        <div class="card-body py-2">
                            <form action="index.php?route=guardar_gasto" method="POST" class="row g-2">
                                <div class="col-4">
                                    <input type="number" step="0.01" name="monto" class="form-control form-control-sm" placeholder="Monto S/" required>
                                </div>
                                <div class="col-5">
                                    <input type="text" name="descripcion" class="form-control form-control-sm" placeholder="Descripción" required>
                                </div>
                                <div class="col-3">
                                    <button type="submit" class="btn btn-warning btn-sm w-100">Agregar</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- FORMULARIO CIERRE -->
                    <form action="index.php?route=guardar_cierre" method="POST" onsubmit="return validarCierre()">
                        <input type="hidden" name="id_caja" value="<?php echo $caja['id']; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-danger fs-5">
                                <i class="bi bi-cash-coin"></i> Dinero Físico en Cajón (cuéntalo):
                            </label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">S/</span>
                                <input type="number" step="0.01" name="monto_final" id="monto_final"
                                       class="form-control fw-bold text-end fs-4" required placeholder="0.00"
                                       oninput="calcularDiferencia()">
                            </div>
                        </div>

                        <div id="diferencia_box" class="alert d-none mb-3">
                            <div class="d-flex justify-content-between">
                                <span>Diferencia:</span>
                                <strong id="diferencia_txt"></strong>
                            </div>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-danger btn-lg">
                                <i class="bi bi-door-closed-fill"></i> CERRAR TURNO
                            </button>
                            <a href="index.php?route=dashboard" class="btn btn-outline-secondary">Cancelar</a>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
const esperado = <?php echo $total_esperado_en_cajon; ?>;

function calcularDiferencia() {
    const ingresado = parseFloat(document.getElementById('monto_final').value) || 0;
    const dif = ingresado - esperado;
    const box = document.getElementById('diferencia_box');
    const txt = document.getElementById('diferencia_txt');
    box.classList.remove('d-none', 'alert-success', 'alert-danger', 'alert-warning');
    txt.textContent = 'S/ ' + dif.toFixed(2);
    if (Math.abs(dif) < 0.01) {
        box.classList.add('alert-success'); txt.textContent = '✅ S/ 0.00 — Cuadra perfectamente';
    } else if (dif > 0) {
        box.classList.add('alert-warning'); txt.textContent = '⚠️ Sobrante: S/ ' + dif.toFixed(2);
    } else {
        box.classList.add('alert-danger'); txt.textContent = '❌ Faltante: S/ ' + Math.abs(dif).toFixed(2);
    }
    box.classList.remove('d-none');
}

function validarCierre() {
    const ingresado = parseFloat(document.getElementById('monto_final').value) || 0;
    const dif = Math.abs(ingresado - esperado);
    if (dif > 50) {
        return confirm('⚠️ Hay una diferencia de S/ ' + dif.toFixed(2) + ' con lo esperado.\n¿Confirmas el cierre de todas formas?');
    }
    return confirm('¿Confirmas el cierre de caja?');
}
</script>

<?php require_once '../app/views/includes/footer.php'; ?>
