<?php
class ConfigHelper {
    private static $config = null;
    
    public static function get($db) {
        if (self::$config === null) {
            try {
                $stmt = $db->query("SELECT 
                    ruc,
                    razon_social as nombre_empresa,
                    direccion,
                    telefono,
                    email
                    FROM configuracion_empresa LIMIT 1");
                self::$config = $stmt->fetch(PDO::FETCH_ASSOC) ?: [
                    'nombre_empresa' => "Macho's System",
                    'ruc' => '',
                    'direccion' => '',
                    'telefono' => '',
                    'email' => ''
                ];
            } catch (Exception $e) {
                self::$config = [
                    'nombre_empresa' => "Macho's System",
                    'ruc' => '',
                    'direccion' => '',
                    'telefono' => '',
                    'email' => ''
                ];
            }
        }
        return self::$config;
    }
}
?>