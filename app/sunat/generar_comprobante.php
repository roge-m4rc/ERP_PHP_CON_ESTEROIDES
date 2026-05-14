<?php
// app/sunat/generar_comprobante.php

require 'config.php';
use Greenter\Model\Client\Client;
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;

// Aquí debes recibir el ID de la venta y buscarla en tu BD usando tu VentaModel
// Para el ejemplo, simularemos las variables que sacaste de tu BD:
$tipo_comprobante = '03'; // Boleta
$serie = 'B001';
$correlativo = '000001';
$total = 118.00;
$op_gravadas = 100.00;
$igv_total = 18.00;

// 1. Cliente
$client = new Client();
$client->setTipoDoc('1') // 1: DNI, 6: RUC
    ->setNumDoc('12345678')
    ->setRznSocial('JUAN PEREZ');

// 2. Comprobante (Factura o Boleta)
$invoice = new Invoice();
$invoice
    ->setUblVersion('2.1')
    ->setTipoOperacion('0101') // Venta Interna
    ->setTipoDoc($tipo_comprobante)
    ->setSerie($serie)
    ->setCorrelativo($correlativo)
    ->setFechaEmision(new DateTime('now', new DateTimeZone('America/Lima')))
    ->setTipoMoneda('PEN')
    ->setCompany(SunatConfig::getEmpresa())
    ->setClient($client)
    ->setMtoOperGravadas($op_gravadas)
    ->setMtoIGV($igv_total)
    ->setTotalImpuestos($igv_total)
    ->setValorVenta($op_gravadas)
    ->setSubTotal($total)
    ->setMtoImpVenta($total);

// 3. Detalle de los productos
$item = new SaleDetail();
$item->setCodProducto('P001')
    ->setUnidad('NIU') // NIU = Unidades
    ->setCantidad(1)
    ->setDescripcion('POLO OVERSIZE NEGRO')
    ->setMtoBaseIgv(100.00)
    ->setPorcentajeIgv(18.00)
    ->setIgv(18.00)
    ->setTipAfeIgv('10') // 10 = Gravado - Operación Onerosa
    ->setTotalImpuestos(18.00)
    ->setMtoValorVenta(100.00) // (Cantidad * Precio sin IGV)
    ->setMtoValorUnitario(100.00) // Precio sin IGV
    ->setMtoPrecioUnitario(118.00); // Precio con IGV

$invoice->setDetails([$item]);

// 4. Leyenda (Obligatorio en Perú, el total en letras)
$legend = (new Legend())
    ->setCode('1000') // 1000 = Monto en letras
    ->setValue('CIENTO DIECIOCHO CON 00/100 SOLES');
$invoice->setLegends([$legend]);

echo "<h3>✅ Objeto Invoice (XML) creado en memoria exitosamente.</h3>";
// El XML ya está listo para ser enviado en la Fase 3.
?>