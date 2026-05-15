<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Macho's Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f6f9; display: flex; align-items: center; justify-content: center; height: 100vh; }
        .login-card { width: 100%; max-width: 400px; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); background: white; }
        .brand-logo { text-align: center; margin-bottom: 20px; font-weight: bold; color: #333; font-size: 24px; }
    </style>
</head>
<body>

<div class="login-card">
    <div class="brand-logo">Sistema de control y ventas👔</div>
    <h5 class="text-center mb-4">Iniciar Sesión</h5>
    
    <?php if(isset($error)): ?>
        <div class="alert alert-danger"><?php echo $error; ?></div>
    <?php endif; ?>

    <form action="index.php?route=login" method="POST">
        <div class="mb-3">
            <label for="usuario" class="form-label">Usuario</label>
            <input type="text" class="form-control" name="usuario" required autofocus>
        </div>
        <div class="mb-3">
            <label for="password" class="form-label">Contraseña</label>
            <input type="password" class="form-control" name="password" required>
        </div>
        <div class="d-grid">
            <button type="submit" class="btn btn-dark">Ingresar al Sistema</button>
        </div>
    </form>
</div>

</body>
</html>