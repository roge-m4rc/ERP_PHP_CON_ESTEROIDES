<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Ropa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body { min-height: 100vh; display: flex; flex-direction: column; }
        .wrapper { display: flex; flex: 1; }
        .sidebar { 
            min-width: 250px; 
            background: #212529; 
            color: white; 
            min-height: 100vh;
            position: relative;
            z-index: 100;
        }
        .sidebar a { 
            color: #adb5bd; 
            text-decoration: none; 
            padding: 10px 15px; 
            display: block;
            position: relative;
            z-index: 101;
        }
        .sidebar a:hover, .sidebar a.active { 
            background: #343a40; 
            color: white; 
        }
        .content { 
            flex: 1; 
            padding: 20px; 
            background: #f8f9fa; 
        }
        .blink { 
            animation: blinker 1.5s linear infinite; 
        } 
        @keyframes blinker { 
            50% { opacity: 0; } 
        }
    </style>
</head>
<body>
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark border-bottom border-secondary">
        <div class="container-fluid">
            <?php
            // Obtener nombre de empresa desde configuracion_empresa
            $navDb = new Database();
            $navConn = $navDb->getConnection();
            $navNombre = "Macho's System";
            if ($navConn) {
                $navStmt = $navConn->query("SELECT razon_social FROM configuracion_empresa LIMIT 1");
                $navCfg = $navStmt->fetch(PDO::FETCH_ASSOC);
                if ($navCfg && !empty($navCfg['razon_social'])) {
                    $navNombre = $navCfg['razon_social'];
                }
            }
            ?>
            <a class="navbar-brand" href="index.php?route=dashboard">👔 <?php echo htmlspecialchars($navNombre); ?></a>
            <div class="d-flex text-white">
                <span class="me-3">
                    <i class="bi bi-person-circle"></i> 
                    <?php echo $_SESSION['user_nombre'] ?? 'Usuario'; ?>
                </span>
            </div>
        </div>
    </nav>
    
    <div class="wrapper">