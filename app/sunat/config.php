<?php
require __DIR__ . '/../../vendor/autoload.php';

use Greenter\See;
use Greenter\Ws\Services\SunatEndpoints;

class SunatConfig {
    public static function getSee($config_db) {
        $see = new See();
        
        // 1. Certificado dinámico
        $ruta_cert = __DIR__ . '/' . $config_db['certificado'];
        $see->setCertificate(file_get_contents($ruta_cert));
        
        // 2. Entorno dinámico (BETA o PRODUCCION)
        if ($config_db['entorno'] === 'PRODUCCION') {
            $see->setService(SunatEndpoints::FE_PRODUCCION);
        } else {
            $see->setService(SunatEndpoints::FE_BETA);
        }
        
        // 3. Credenciales dinámicas
        $see->setClaveSOL($config_db['ruc'], $config_db['sol_usuario'], $config_db['sol_clave']);
        
        return $see;
    }

    public static function getEmpresa($config_db) {
        return (new \Greenter\Model\Company\Company())
            ->setRuc($config_db['ruc'])
            ->setRazonSocial($config_db['nombre_empresa'])
            ->setNombreComercial($config_db['nombre_empresa'])
            ->setAddress((new \Greenter\Model\Company\Address())
                ->setUbigueo('050101') 
                ->setDepartamento('AYACUCHO')
                ->setProvincia('HUAMANGA')
                ->setDistrito('AYACUCHO')
                ->setDireccion($config_db['direccion']));
    }
}
?>