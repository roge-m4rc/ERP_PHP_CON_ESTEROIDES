<?php
class Database {
    private $conn;

    public function getConnection() {
        $this->conn = null;

        // Obtener la variable de entorno de Railway
        $dbUrl = getenv('DATABASE_URL');

        if (!$dbUrl) {
            die("Error: No se encontró la variable DATABASE_URL.");
        }

        // Desarmar la URL para extraer las piezas
        $dbopts = parse_url($dbUrl);

        $host = $dbopts["host"];
        $port = $dbopts["port"];
        $user = $dbopts["user"];
        $pass = $dbopts["pass"];
        $dbname = ltrim($dbopts["path"], '/');

        try {
            // Conexión a PostgreSQL (Supabase)
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $this->conn = new PDO($dsn, $user, $pass);
            
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            
        } catch(PDOException $exception) {
            die("Error de conexión a la Base de Datos: " . $exception->getMessage());
        }

        return $this->conn;
    }
}
?>