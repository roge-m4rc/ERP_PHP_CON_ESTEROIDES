<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ticket #<?php echo str_pad($venta['id'],5,"0",STR_PAD_LEFT); ?></title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Courier New',Courier,monospace; background:#e0e0e0; display:flex; justify-content:center; align-items:flex-start; min-height:100vh; padding:20px; }
        .ticket { width:320px; background:#fff; padding:16px; box-shadow:0 2px 8px rgba(0,0,0,.3); }
        .header { text-align:center; border-bottom:2px dashed #000; padding-bottom:10px; margin-bottom:10px; }
        .header h3 { font-size:18px; letter-spacing:2px; }
        .header p { font-size:11px; line-height:1.5; color:#555; }
        .info { font-size:11px; margin-bottom:10px; line-height:1.7; }
        .info span { display:block; }
        table { width:100%; font-size:11px; border-collapse:collapse; }
        thead tr { border-bottom:1px solid #000; border-top:1px solid #000; }
        th, td { padding:3px 2px; }
        td.price { text-align:right; }
        th.price { text-align:right; }
        .separator { border-top:1px dashed #000; margin:6px 0; }
        .total-row td { font-weight:bold; font-size:13px; border-top:2px solid #000; padding-top:5px; }
        .descuento-row td { color:#c00; font-size:11px; }
        .footer { text-align:center; border-top:2px dashed #000; padding-top:10px; margin-top:10px; font-size:11px; color:#555; line-height:1.6; }
        .metodo { display:inline-block; background:#333; color:#fff; padding:1px 6px; border-radius:3px; font-size:10px; margin-top:3px; }
        .acciones { text-align:center; margin-top:15px; display:flex; gap:8px; justify-content:center; }
        .acciones button { padding:8px 16px; border:none; cursor:pointer; border-radius:4px; font-size:12px; font-weight:bold; }
        .btn-print { background:#333; color:#fff; }
        .btn-close-w { background:#e0e0e0; color:#333; }
        @media print {
            body { background:white; padding:0; }
            .ticket { box-shadow:none; width:100%; max-width:320px; }
            .acciones { display:none !important; }
        }
    </style>
</head>
<body>
<div class="ticket">
    <div class="header">
        <h3><?php echo strtoupper(htmlspecialchars($config['nombre_empresa'] ?? "MACHO'S")); ?></h3>
        <p>
            <?php if(!empty($config['ruc'])): ?>RUC: <?php echo $config['ruc']; ?><br><?php endif; ?>
            <?php if(!empty($config['direccion'])): ?><?php echo htmlspecialchars($config['direccion']); ?><br><?php endif; ?>
            <?php if(!empty($config['telefono'])): ?>Tel: <?php echo $config['telefono']; ?><?php endif; ?>
        </p>
    </div>

    <div class="info">
        <span><strong>Ticket:</strong> #<?php echo str_pad($venta['id'],5,"0",STR_PAD_LEFT); ?> — <?php echo $venta['serie'] . '-' . $venta['correlativo']; ?></span>
        <span><strong>Fecha:</strong> <?php echo date("d/m/Y H:i", strtotime($venta['fecha'])); ?></span>
        <span><strong>Atendido por:</strong> <?php echo htmlspecialchars($venta['vendedor']); ?></span>
        <span><strong>Cliente:</strong> <?php echo htmlspecialchars($venta['cliente_nombre']); ?></span>
        <span><strong>Pago:</strong> <span class="metodo"><?php echo $venta['metodo_pago']; ?></span></span>
        <?php if($venta['estado'] == 0): ?>
        <span style="color:red;font-weight:bold;text-align:center;border:1px solid red;padding:2px;margin-top:4px;">⚠️ TICKET ANULADO</span>
        <?php endif; ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="text-align:left;width:45%">Producto</th>
                <th style="text-align:center;width:15%">Cant</th>
                <th class="price" style="width:20%">P.Unit</th>
                <th class="price" style="width:20%">Subt.</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $subtotal_items = 0;
            foreach($detalles as $d): 
                $subtotal_items += $d['total'];
            ?>
            <tr>
                <td>
                    <?php echo htmlspecialchars($d['nombre']); ?><br>
                    <small style="color:#777;"><?php echo $d['talla'] ? $d['talla'] : '—'; ?>/<?php echo $d['color'] ? $d['color'] : '—'; ?></small>
                </td>
                <td style="text-align:center;"><?php echo $d['cantidad']; ?></td>
                <td class="price"><?php echo number_format($d['precio_unitario'],2); ?></td>
                <td class="price"><?php echo number_format($d['total'],2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
        <tfoot>
            <tr><td colspan="4"><div class="separator"></div></td></tr>
            <tr style="font-size:11px;">
                <td colspan="3" style="text-align:right;">Subtotal:</td>
                <td class="price">S/ <?php echo number_format($subtotal_items, 2); ?></td>
            </tr>
            <?php if(($venta['descuento']??0) > 0): ?>
            <tr class="descuento-row">
                <td colspan="3" style="text-align:right;">Descuento:</td>
                <td class="price">- S/ <?php echo number_format($venta['descuento'],2); ?></td>
            </tr>
            <?php endif; ?>
            <tr class="total-row">
                <td colspan="2">TOTAL A PAGAR:</td>
                <td colspan="2" class="price">S/ <?php echo number_format($venta['total'],2); ?></td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        ¡Gracias por su compra!<br>
        No se aceptan devoluciones<br>
        sin presentar este ticket.<br>
        <small style="color:#aaa;"><?php echo date("d/m/Y H:i:s"); ?></small>
    </div>

    <div style="text-align: center; margin-top: 10px;">
        <div id="qrcode" style="display: inline-block;"></div>
        <p style="font-size: 10px;">Representación impresa de la <br> 
            <?= ($venta['tipo_comprobante'] == '01') ? 'FACTURA' : 'BOLETA' ?> ELECTRÓNICA
        </p>
    </div>
</div>

<div class="acciones">
    <button class="btn-print" onclick="window.print()">🖨️ Imprimir</button>
    <button class="btn-close-w" onclick="window.close()">✕ Cerrar</button>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
    const rucEmisor = "<?= $config['ruc'] ?>";
    const tipoComp = "<?= $venta['tipo_comprobante'] ?>";
    const serie = "<?= $venta['serie'] ?>";
    const correlativo = "<?= $venta['correlativo'] ?>";
    const igv = "<?= number_format($venta['igv'], 2, '.', '') ?>";
    const total = "<?= number_format($venta['total'], 2, '.', '') ?>";
    const fecha = "<?= date('Y-m-d', strtotime($venta['fecha'])) ?>";
    const tipoDocCli = "<?= $venta['cliente_tipo_doc'] ?>";
    const numDocCli = "<?= $venta['cliente_num_doc'] ?>";
    const cadenaQR = `${rucEmisor}|${tipoComp}|${serie}|${correlativo}|${igv}|${total}|${fecha}|${tipoDocCli}|${numDocCli}|`;
    new QRCode(document.getElementById("qrcode"), {
        text: cadenaQR,
        width: 100,
        height: 100,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.M
    });
</script>

</body>
</html>