<?php
class SunatHelper {
    
    public static function emitirComprobante($ventaData, $detallesData, $configData) {
        
        // Validar que tengamos configuración
        if (empty($configData) || empty($configData['ruc'])) {
            return [
                'ok' => false,
                'estado_sunat' => 'PENDIENTE',
                'msg' => 'No hay configuración SUNAT. Configure RUC y credenciales en Configuración > SUNAT.',
                'xml_generado' => false
            ];
        }

        // Validar datos mínimos de venta
        if (empty($ventaData['cliente_num_doc']) || $ventaData['cliente_num_doc'] == '-') {
            return [
                'ok' => false,
                'estado_sunat' => 'PENDIENTE',
                'msg' => 'Cliente sin documento válido. Se registró la venta pero no se envió a SUNAT.',
                'xml_generado' => false
            ];
        }

        // Crear carpetas si no existen
        $ruta_xml = __DIR__ . '/../../public/sunat/xml/';
        $ruta_cdr = __DIR__ . '/../../public/sunat/cdr/';
        
        if (!is_dir($ruta_xml)) {
            mkdir($ruta_xml, 0755, true);
        }
        if (!is_dir($ruta_cdr)) {
            mkdir($ruta_cdr, 0755, true);
        }

        // Generar nombre del XML
        $nombre_xml = $configData['ruc'] . '-' . 
                      $ventaData['tipo_comprobante'] . '-' . 
                      $ventaData['serie'] . '-' . 
                      str_pad($ventaData['correlativo'], 8, '0', STR_PAD_LEFT);

        $ruta_archivo = $ruta_xml . $nombre_xml . '.xml';

        // Generar XML (simplificado para pruebas)
        $xml = self::generarXML($ventaData, $detallesData, $configData);

        // Guardar XML
        if (file_put_contents($ruta_archivo, $xml) === false) {
            return [
                'ok' => false,
                'estado_sunat' => 'ERROR_XML',
                'msg' => 'No se pudo guardar el XML. Verifique permisos de carpetas.',
                'xml_generado' => false
            ];
        }

        // Simular envío a SUNAT (cuando tengas certificado real, reemplazar esto)
        $resultado_simulado = self::simularEnvioSUNAT($ventaData, $configData);

        return [
            'ok' => $resultado_simulado['exito'],
            'estado_sunat' => $resultado_simulado['exito'] ? 'ACEPTADO' : 'RECHAZADO',
            'msg' => $resultado_simulado['mensaje'],
            'xml_generado' => true,
            'xml_path' => $ruta_archivo,
            'cdr' => $resultado_simulado['cdr'] ?? null
        ];
    }

    private static function generarXML($venta, $detalles, $config) {
        // XML simplificado para pruebas
        $fecha = date('Y-m-d', strtotime($venta['fecha']));
        $hora = date('H:i:s', strtotime($venta['fecha']));
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<Invoice xmlns="urn:oasis:names:specification:ubl:schema:xsd:Invoice-2">' . "\n";
        $xml .= '  <ID>' . $venta['serie'] . '-' . str_pad($venta['correlativo'], 8, '0', STR_PAD_LEFT) . '</ID>' . "\n";
        $xml .= '  <IssueDate>' . $fecha . '</IssueDate>' . "\n";
        $xml .= '  <Company>' . htmlspecialchars($config['razon_social'] ?? 'EMPRESA') . '</Company>' . "\n";
        $xml .= '  <Customer>' . htmlspecialchars($venta['cliente_nombre'] ?? 'General') . '</Customer>' . "\n";
        $xml .= '  <Total>' . number_format($venta['total'], 2) . '</Total>' . "\n";
        $xml .= '</Invoice>';

        return $xml;
    }

    private static function simularEnvioSUNAT($venta, $config) {
        // SIMULACIÓN - Reemplazar con lógica real cuando tengas certificado
        sleep(1);
        
        // 90% éxito si hay datos completos
        $datos_completos = !empty($config['ruc']) && 
                          !empty($config['usuario_sol']) && 
                          $venta['cliente_num_doc'] != '-' &&
                          strlen($venta['cliente_num_doc']) >= 8;

        if ($datos_completos && rand(1, 100) <= 90) {
            return [
                'exito' => true,
                'mensaje' => 'Comprobante aceptado por SUNAT (simulado)',
                'cdr' => 'CDR-' . date('Ymd') . '-' . rand(10000, 99999)
            ];
        } else {
            return [
                'exito' => false,
                'mensaje' => 'Datos incompletos o error de conexión con SUNAT (simulado)',
                'cdr' => null
            ];
        }
    }
}
?>