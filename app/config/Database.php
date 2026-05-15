<?php
// 1. Obtener la variable de entorno que configuramos en Railway
$dbUrl = getenv('DATABASE_URL');

if (!$dbUrl) {
    die("Error: No se encontró la variable DATABASE_URL.");
}

// 2. Desarmar la URL para extraer las piezas
$dbopts = parse_url($dbUrl);

$host = $dbopts["host"];
$port = $dbopts["port"];
$user = $dbopts["user"];
$pass = $dbopts["pass"];
$dbname = ltrim($dbopts["path"], '/'); // Quita la barra inicial

// 3. Crear la conexión PDO usando el dialecto "pgsql" (PostgreSQL)
try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $pass);
    
    // Configurar PDO para que lance errores y trabajar con caracteres UTF-8
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Si algo falla, mostrará el error en la pantalla para poder solucionarlo
    die("Fallo en la conexión a Supabase: " . $e->getMessage());
}
?>