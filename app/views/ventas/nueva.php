<?php require_once '../app/views/includes/header.php'; ?>
<?php require_once '../app/views/includes/sidebar.php'; ?>

<style>
    .blink { animation: blinker 1.5s linear infinite; }
    @keyframes blinker { 50% { opacity: 0; } }
    
    /* Grid de productos - SOLUCIÓN SOBREPOSICIÓN */
    #productosGrid {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        max-height: 600px;
        overflow-y: auto;
        padding-right: 5px;
    }
    
    .producto-item {
        width: calc(50% - 0.5rem); /* 2 columnas en md */
        margin-bottom: 0;
    }
    
    @media (min-width: 992px) {
        .producto-item {
            width: calc(33.333% - 0.667rem); /* 3 columnas en lg */
        }
    }
    
    .producto-card {
        transition: all 0.2s;
        cursor: pointer;
        border: 2px solid #e0e0e0;
        height: 100%;
        display: flex;
        flex-direction: column;
    }
    
    .producto-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.15);
        border-color: #0d6efd;
    }
    
    .producto-card.seleccionado {
        border-color: #198754;
        background-color: #f8fff9;
    }
    
    .producto-card .card-body {
        padding: 1rem;
        display: flex;
        flex-direction: column;
        flex: 1;
    }
    
    .stock-badge {
        position: absolute;
        top: 10px;
        right: 10px;
        z-index: 2;
    }
    
    .cantidad-control {
        display: flex;
        align-items: center;
        gap: 5px;
        justify-content: center;
    }
    
    .cantidad-control input {
        width: 50px;
        text-align: center;
        font-weight: bold;
        padding: 2px;
    }
    
    .cantidad-control button {
        padding: 2px 8px;
        font-size: 14px;
        line-height: 1;
    }
    
    .variante-info {
        font-size: 0.8rem;
        margin-bottom: 8px;
    }
    
    .precio-tag {
        font-size: 1.1rem;
        font-weight: bold;
        color: #198754;
        margin-bottom: 8px;
    }
    
    /* Botón agregar siempre abajo */
    .agregar-form {
        margin-top: auto;
    }
    
    .pagination .page-link {
        cursor: pointer;
    }
</style>

<div class="content">
    <div class="row h-100">
        <!-- COLUMNA IZQUIERDA: CATÁLOGO -->
        <div class="col-md-7">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <span><i class="bi bi-grid-3x3-gap"></i> Catálogo de Productos</span>
                    <span class="badge bg-light text-dark" id="totalProductos">0 productos</span>
                </div>
                <div class="card-body">
                    <!-- Buscador -->
                    <form action="index.php" method="GET" class="mb-3">
                        <input type="hidden" name="route" value="nueva_venta">
                        <div class="input-group">
                            <input type="text" name="buscar" class="form-control" 
                                   placeholder="Buscar por nombre, talla, color o código..." 
                                   autofocus autocomplete="off" 
                                   value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                            <button class="btn btn-primary" type="submit"><i class="bi bi-search"></i></button>
                            <?php if(isset($_GET['buscar']) && $_GET['buscar']): ?>
                            <a href="index.php?route=nueva_venta" class="btn btn-outline-secondary">Limpiar</a>
                            <?php endif; ?>
                        </div>
                    </form>

                    <!-- Filtros rápidos -->
                    <div class="mb-3 d-flex gap-2 flex-wrap">
                        <button class="btn btn-sm btn-outline-dark active" onclick="filtrarCategoria('todos')">Todos</button>
                        <?php 
                        // Obtener categorías únicas de los resultados
                        $categorias_unicas = [];
                        if(isset($resultados) && !empty($resultados)) {
                            foreach($resultados as $prod) {
                                $cat = $prod['categoria'] ?? 'General';
                                if(!in_array($cat, $categorias_unicas)) $categorias_unicas[] = $cat;
                            }
                            foreach($categorias_unicas as $cat): 
                        ?>
                        <button class="btn btn-sm btn-outline-dark" onclick="filtrarCategoria('<?php echo htmlspecialchars($cat); ?>')">
                            <?php echo htmlspecialchars($cat); ?>
                        </button>
                        <?php 
                            endforeach;
                        }
                        ?>
                    </div>

                    <!-- Grid de productos -->
                    <div id="productosGrid" class="row g-3" style="max-height: 550px; overflow-y: auto;">
                        <?php if(isset($resultados) && !empty($resultados)): ?>
                            <?php foreach($resultados as $index => $prod): ?>
                            <div class="col-md-6 col-lg-4 producto-item" 
                                data-categoria="<?php echo htmlspecialchars($prod['categoria'] ?? 'General'); ?>"
                                data-index="<?php echo $index; ?>">
                                <div class="card producto-card h-100" onclick="seleccionarProducto(<?php echo $prod['id']; ?>)">
                                    <div class="card-body p-3 position-relative">
                                        
                                        <!-- Stock badge -->
                                        <?php if($prod['stock'] <= 5): ?>
                                        <span class="badge bg-danger stock-badge blink">¡<?php echo $prod['stock']; ?> left!</span>
                                        <?php else: ?>
                                        <span class="badge bg-success stock-badge"><?php echo $prod['stock']; ?> disp.</span>
                                        <?php endif; ?>

                                        <!-- Info producto -->
                                        <h6 class="card-title mb-1 fw-bold" style="min-height: 40px;"><?php echo htmlspecialchars($prod['nombre']); ?></h6>
                                        
                                        <div class="variante-info">
                                            <span class="badge bg-secondary me-1"><?php echo htmlspecialchars($prod['talla']); ?></span>
                                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($prod['color']); ?></span>
                                        </div>
                                        
                                        <div class="mb-2 text-muted small">
                                            Cód: <?php echo htmlspecialchars($prod['codigo'] ?? 'N/A'); ?>
                                        </div>
                                        
                                        <div class="precio-tag">S/ <?php echo number_format($prod['precio_venta'], 2); ?></div>
                                        
                                        <form action="index.php?route=agregar_carrito" method="POST" 
                                            class="agregar-form" 
                                            onsubmit="return validarCantidad(this)"
                                            onclick="event.stopPropagation();">
                                            
                                            <input type="hidden" name="id_variante" value="<?php echo $prod['id']; ?>">
                                            <input type="hidden" name="precio" value="<?php echo $prod['precio_venta']; ?>">
                                            <input type="hidden" name="nombre_completo" value="<?php echo htmlspecialchars($prod['nombre'] . ' (' . $prod['talla'] . ' / ' . $prod['color'] . ')'); ?>">
                                            <input type="hidden" name="search_term" value="<?php echo isset($_GET['buscar']) ? htmlspecialchars($_GET['buscar']) : ''; ?>">
                                            
                                            <div class="cantidad-control mb-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cambiarCantidad(this, -1)">−</button>
                                                <input type="number" name="cantidad" value="1" min="1" max="<?php echo $prod['stock']; ?>" class="form-control form-control-sm">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" onclick="cambiarCantidad(this, 1)">+</button>
                                            </div>
                                            
                                            <button type="submit" class="btn btn-sm btn-primary w-100">
                                                <i class="bi bi-cart-plus"></i> Agregar
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        <?php elseif(isset($_GET['buscar'])): ?>
                            <div class="col-12">
                                <div class="alert alert-warning text-center">
                                    <i class="bi bi-search fs-1"></i><br>
                                    No se encontraron productos con "<strong><?php echo htmlspecialchars($_GET['buscar']); ?></strong>"
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="col-12 text-center text-muted py-5">
                                <i class="bi bi-upc-scan fs-1"></i>
                                <p class="mt-2">Escribe en el buscador para encontrar productos</p>
                                <small class="text-muted">Puedes buscar por nombre, talla, color o código</small>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Paginación -->
                    <?php if(isset($resultados) && count($resultados) > 0): ?>
                    <nav class="mt-3" id="navPaginacion" style="display:none;">
                        <ul class="pagination justify-content-center" id="listaPaginacion"></ul>
                    </nav>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- COLUMNA DERECHA: TICKET -->
        <div class="col-md-5">
            <div class="card shadow-lg border-success h-100">
                <div class="card-header bg-success text-white d-flex justify-content-between">
                    <span><i class="bi bi-cart4"></i> Ticket Actual</span>
                    <div>
                        <button type="button" class="btn btn-sm btn-danger me-2" data-bs-toggle="modal" data-bs-target="#modalGasto">
                            <i class="bi bi-cash-stack"></i> Gasto
                        </button>
                        <a href="index.php?route=limpiar_carrito" class="btn btn-sm btn-outline-light" onclick="return confirm('¿Vaciar carrito?')">Limpiar</a>
                    </div>
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="flex-grow-1 overflow-auto" style="max-height: 350px;">
                        <table class="table table-sm table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">Cant.</th>
                                    <th>Producto</th>
                                    <th width="80" class="text-end">Total</th>
                                    <th width="30"></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(!empty($_SESSION['carrito'])): ?>
                                    <?php foreach($_SESSION['carrito'] as $idx => $item): ?>
                                    <tr>
                                        <td>
                                            <span class="badge bg-primary"><?php echo $item['cantidad']; ?></span>
                                        </td>
                                        <td class="small"><?php echo htmlspecialchars($item['nombre']); ?></td>
                                        <td class="text-end fw-bold">S/ <?php echo number_format($item['subtotal'], 2); ?></td>
                                        <td>
                                            <a href="index.php?route=quitar_carrito&idx=<?php echo $idx; ?>&buscar=<?php echo isset($_GET['buscar']) ? urlencode($_GET['buscar']) : ''; ?>" 
                                               class="btn btn-sm btn-link text-danger p-0" 
                                               onclick="return confirm('¿Quitar este item?')"
                                               title="Quitar">
                                                <i class="bi bi-x-circle"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="4" class="text-center text-muted py-4">
                                        <i class="bi bi-cart-x fs-2"></i><br>Carrito vacío
                                    </td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Totales -->
                    <div class="mt-auto border-top pt-3">
                        <?php 
                        $total_items = 0;
                        $total_cantidad = 0;
                        if(!empty($_SESSION['carrito'])) {
                            foreach($_SESSION['carrito'] as $item) {
                                $total_items++;
                                $total_cantidad += $item['cantidad'];
                            }
                        }
                        ?>
                        <div class="d-flex justify-content-between small text-muted mb-2">
                            <span><?php echo $total_items; ?> items / <?php echo $total_cantidad; ?> unidades</span>
                            <span>Subtotal:</span>
                        </div>
                        
                        <form action="index.php?route=finalizar_venta" method="POST">
                            <input type="hidden" id="subtotal_hidden" value="<?php echo isset($total_venta) ? $total_venta : 0; ?>">

                            <!-- Comprobante -->
                            <div class="row g-2 mb-2">
                                <div class="col-4">
                                    <label class="small">Comp.</label>
                                    <select name="tipo_comprobante" id="tipo_comprobante" class="form-select form-select-sm" onchange="cambiarTipoDoc()">
                                        <option value="03">BOLETA</option>
                                        <option value="01">FACTURA</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="small">Doc.</label>
                                    <select name="cliente_tipo_doc" id="cliente_tipo_doc" class="form-select form-select-sm">
                                        <option value="1">DNI</option>
                                        <option value="6">RUC</option>
                                    </select>
                                </div>
                                <div class="col-4">
                                    <label class="small">Número</label>
                                    <div class="input-group input-group-sm">
                                        <input type="text" name="cliente_num_doc" id="cliente_num_doc" class="form-control" placeholder="00000000" value="-">
                                        <button type="button" id="btn_buscar_doc" class="btn btn-primary" onclick="buscarCliente()">🔍</button>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-2">
                                <input type="text" name="cliente_nombre" id="cliente_nombre" class="form-control form-control-sm" placeholder="Nombre / Razón Social" value="Público General">
                            </div>

                            <!-- Descuento -->
                            <div class="mb-2">
                                <label class="small text-danger">Descuento S/</label>
                                <input type="number" step="0.50" min="0" max="<?php echo isset($total_venta) ? $total_venta : 0; ?>" 
                                       name="descuento" id="input_descuento" class="form-control form-control-sm border-danger" 
                                       value="0.00" oninput="calcularTotal()">
                            </div>

                            <!-- Método pago -->
                            <div class="mb-3">
                                <select name="metodo_pago" class="form-select form-select-sm bg-light fw-bold border-success">
                                    <option value="EFECTIVO">💵 Efectivo</option>
                                    <option value="YAPE">📱 Yape / Plin</option>
                                    <option value="TARJETA">💳 Tarjeta</option>
                                </select>
                            </div>

                            <div class="alert alert-success text-center py-2">
                                <small>Total a Pagar:</small>
                                <h3 class="fw-bold mb-0" id="total_display">S/ <?php echo isset($total_venta) ? number_format($total_venta, 2) : '0.00'; ?></h3>
                            </div>

                            <button type="submit" class="btn btn-success w-100 btn-lg" <?php echo empty($_SESSION['carrito']) ? 'disabled' : ''; ?>>
                                <i class="bi bi-cash-coin"></i> COBRAR
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Gasto -->
<div class="modal fade" id="modalGasto" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title">Registrar Gasto</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="index.php?route=guardar_gasto" method="POST">
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="small">Monto S/</label>
                        <input type="number" step="0.01" name="monto" class="form-control" required>
                    </div>
                    <div class="mb-2">
                        <label class="small">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="2" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-danger">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ==================== PAGINACIÓN ====================
const ITEMS_POR_PAGINA = 9;
let paginaActual = 1;

function initPaginacion() {
    const items = document.querySelectorAll('.producto-item');
    const total = items.length;
    
    if (total <= ITEMS_POR_PAGINA) {
        const nav = document.getElementById('navPaginacion');
        if(nav) nav.style.display = 'none';
        return;
    }
    
    const badge = document.getElementById('totalProductos');
    if(badge) badge.textContent = total + ' productos';
    
    const nav = document.getElementById('navPaginacion');
    if(nav) nav.style.display = 'block';
    
    const totalPaginas = Math.ceil(total / ITEMS_POR_PAGINA);
    mostrarPagina(1);
    generarBotonesPaginacion(totalPaginas);
}

function mostrarPagina(pagina) {
    paginaActual = pagina;
    const items = document.querySelectorAll('.producto-item');
    const inicio = (pagina - 1) * ITEMS_POR_PAGINA;
    const fin = inicio + ITEMS_POR_PAGINA;
    
    items.forEach((item, index) => {
        item.style.display = (index >= inicio && index < fin) ? 'block' : 'none';
    });
    
    // Actualizar botones activos
    document.querySelectorAll('.pagination .page-item').forEach(btn => btn.classList.remove('active'));
    const btnActivo = document.getElementById('pagina-' + pagina);
    if(btnActivo) btnActivo.classList.add('active');
}

function generarBotonesPaginacion(totalPaginas) {
    const lista = document.getElementById('listaPaginacion');
    if(!lista) return;
    
    let html = '';
    
    // Anterior
    html += `<li class="page-item ${paginaActual === 1 ? 'disabled' : ''}">
        <a class="page-link" onclick="mostrarPagina(${Math.max(1, paginaActual - 1)})">«</a>
    </li>`;
    
    // Números
    for(let i = 1; i <= totalPaginas; i++) {
        html += `<li class="page-item" id="pagina-${i}">
            <a class="page-link" onclick="mostrarPagina(${i})">${i}</a>
        </li>`;
    }
    
    // Siguiente
    html += `<li class="page-item ${paginaActual === totalPaginas ? 'disabled' : ''}">
        <a class="page-link" onclick="mostrarPagina(${Math.min(totalPaginas, paginaActual + 1)})">»</a>
    </li>`;
    
    lista.innerHTML = html;
}

// ==================== FILTROS ====================
function filtrarCategoria(categoria) {
    const items = document.querySelectorAll('.producto-item');
    let visibles = 0;
    
    items.forEach(item => {
        if(categoria === 'todos' || item.dataset.categoria === categoria) {
            item.style.display = 'block';
            visibles++;
        } else {
            item.style.display = 'none';
        }
    });
    
    // Reset paginación si hay filtro
    const nav = document.getElementById('navPaginacion');
    if(nav) nav.style.display = visibles > ITEMS_POR_PAGINA ? 'block' : 'none';
    
    if(visibles > ITEMS_POR_PAGINA) {
        mostrarPagina(1);
    }
    
    // Actualizar botones activos
    document.querySelectorAll('.btn-outline-dark').forEach(btn => btn.classList.remove('active'));
    if(event && event.target) event.target.classList.add('active');
}

// ==================== CANTIDAD ====================
function cambiarCantidad(btn, delta) {
    const input = btn.parentElement.querySelector('input');
    let val = parseInt(input.value) + delta;
    const max = parseInt(input.max);
    const min = parseInt(input.min) || 1;
    
    if(val < min) val = min;
    if(val > max) val = max;
    
    input.value = val;
}

function validarCantidad(form) {
    const input = form.querySelector('input[name="cantidad"]');
    const cantidad = parseInt(input.value);
    const max = parseInt(input.max);
    
    if(cantidad > max) {
        alert('¡Stock insuficiente! Solo hay ' + max + ' unidades disponibles.');
        input.value = max;
        return false;
    }
    if(cantidad < 1) {
        alert('La cantidad mínima es 1.');
        input.value = 1;
        return false;
    }
    return true;
}

function seleccionarProducto(id) {
    // Visual feedback
    document.querySelectorAll('.producto-card').forEach(card => card.classList.remove('seleccionado'));
    if(event && event.currentTarget) {
        event.currentTarget.classList.add('seleccionado');
    }
}

// ==================== TOTAL Y COMPROBANTE ====================
function calcularTotal() {
    let subtotal = parseFloat(document.getElementById('subtotal_hidden').value) || 0;
    let descuento = parseFloat(document.getElementById('input_descuento').value) || 0;
    let total = subtotal - descuento;
    if(total < 0) total = 0;
    
    const display = document.getElementById('total_display');
    if(display) display.innerText = "S/ " + total.toFixed(2);
}

function cambiarTipoDoc() {
    let comp = document.getElementById('tipo_comprobante');
    let doc = document.getElementById('cliente_tipo_doc');
    if(!comp || !doc) return;
    
    if(comp.value === '01') {
        doc.value = '6';
        doc.disabled = true;
    } else {
        doc.value = '1';
        doc.disabled = false;
    }
}

// ==================== BUSCAR CLIENTE ====================
function buscarCliente() {
    let tipo = document.getElementById('cliente_tipo_doc');
    let num = document.getElementById('cliente_num_doc');
    let btn = document.getElementById('btn_buscar_doc');
    let inputNombre = document.getElementById('cliente_nombre');
    
    if(!tipo || !num || !btn || !inputNombre) return;
    
    let tipoVal = tipo.value;
    let numVal = num.value.trim();
    
    if(numVal.length < 8) {
        alert("Ingrese un documento válido.");
        return;
    }
    
    btn.innerHTML = "⏳...";
    btn.disabled = true;

    fetch(`index.php?route=consulta_api&tipo=${tipoVal}&doc=${numVal}`)
        .then(response => response.json())
        .then(data => {
            if(data.success) {
                inputNombre.value = data.nombre;
                inputNombre.style.borderColor = "#28a745";
                setTimeout(() => inputNombre.style.borderColor = "", 2000);
            } else {
                alert("❌ " + data.msg);
                inputNombre.value = "";
            }
        })
        .catch(error => {
            console.error("Error:", error);
            alert("Error al conectar con el servidor.");
        })
        .finally(() => {
            btn.innerHTML = "🔍 Buscar";
            btn.disabled = false;
        });
}

// ==================== INICIALIZAR ====================
document.addEventListener('DOMContentLoaded', function() {
    initPaginacion();
    calcularTotal();
});
</script>

<?php require_once '../app/views/includes/footer.php'; ?>