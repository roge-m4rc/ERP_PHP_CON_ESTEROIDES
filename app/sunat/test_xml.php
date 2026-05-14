<?php
require 'config.php';
use Greenter\Model\Sale\Invoice;
use Greenter\Model\Sale\SaleDetail;
use Greenter\Model\Sale\Legend;
use Greenter\Model\Client\Client;

$see = SunatConfig::getSee();

// Simulamos los datos de una venta que ya tienes en tu base de datos
$invoice = new Invoice();
$invoice->setUblVersion('2.1')
    ->setTipoOperacion('0101')
    ->setTipoDoc('03') // Boleta
    ->setSerie('B001')
    ->setCorrelativo('1')
    ->setFechaEmision(new DateTime())
    ->setTipoMoneda('PEN')
    ->setClient((new Client())
        ->setTipoDoc('1') // DNI
        ->setNumDoc('00000000')
        ->setRznSocial('CLIENTE DE PRUEBA'))
    ->setCompany(SunatConfig::getEmpresa())
    ->setMtoOperGravadas(100)
    ->setMtoIGV(18)
    ->setTotalImpuestos(18)
    ->setValorVenta(100)
    ->setSubTotal(118)
    ->setMtoImpVenta(118);

$item = new SaleDetail();
$item->setCodProducto('P001')
    ->setUnidad('NIU')
    ->setCantidad(1)
    ->setDescripcion('PRODUCTO DE PRUEBA')
    ->setMtoBaseIgv(100)
    ->setPorcentajeIgv(18)
    ->setIgv(18)
    ->setTipAfeIgv('10') // Gravado
    ->setTotalImpuestos(18)
    ->setMtoValorUnitario(100)
    ->setMtoPrecioUnitario(118)
    ->setMtoValorVenta(100);

$invoice->setDetails([$item])
    ->setLegends([(new Legend())->setCode('1000')->setValue('CIENTO DIECIOCHO CON 00/100 SOLES')]);

// GENERAR EL XML
$result = $see->send($invoice);

if ($result->isSuccess()) {
    echo "<h1>¡ÉXITO!</h1>";
    echo "XML Generado y aceptado por SUNAT (BETA).<br>";
    echo "Respuesta: " . $result->getCdrResponse()->getDescription();
} else {
    echo "<h1>ERROR</h1>";
    var_dump($result->getError());
}