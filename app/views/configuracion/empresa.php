<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Configuración Empresa / SUNAT</title>
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'Segoe UI', sans-serif; background: #f5f6fa; margin: 0; padding: 20px; }
        .container { max-width: 900px; margin: 0 auto; }
        .card { background: white; border-radius: 12px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
        h2 { color: #2c3e50; margin-top: 0; border-bottom: 2px solid #3498db; padding-bottom: 10px; }
        .form-group { margin-bottom: 15px; }
        label { display: block; font-weight: 600; margin-bottom: 5px; color: #555; }
        input[type="text"], input[type="email"], input[type="password"], input[type="number"], input[type="file"] {
            width: 100%; padding: 10px 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px;
        }
        input:focus { border-color: #3498db; outline: none; }
        .row { display: flex; gap: 15px; }
        .row .form-group { flex: 1; }
        button { background: #3498db; color: white; border: none; padding: 12px 30px; border-radius: 8px; cursor: pointer; font-size: 15px; font-weight: 600; }
        button:hover { background: #2980b9; }
        .btn-success { background: #27ae60; }
        .btn-success:hover { background: #219a52; }
        .alert { padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background: #f8f9fa; font-weight: 600; color: #555; }
        .nav-back { display: inline-block; margin-bottom: 20px; color: #3498db; text-decoration: none; font-weight: 500; }
        .nav-back:hover { text-decoration: underline; }
        .cert-info { background: #e8f4f8; padding: 15px; border-radius: 8px; color: #0c5460; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .badge-factura { background: #e3f2fd; color: #1565c0; }
        .badge-boleta { background: #fff3e0; color: #e65100; }
    </style>
</head>
<body>
<div class="container">
    <a href="index.php?route=dashboard" class="nav-back">← Volver al Dashboard</a>

    <?php if (isset($_GET['ok'])): ?>
        <div class="alert alert-success">✅ Configuración guardada correctamente.</div>
    <?php endif; ?>
    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger">❌ Error al procesar la solicitud.</div>
    <?php endif; ?>

    <!-- DATOS DE LA EMPRESA -->
    <div class="card">
        <h2>🏢 Datos de la Empresa</h2>
        <form method="POST" action="index.php?route=guardar_empresa">
            <div class="row">
                <div class="form-group">
                    <label>RUC</label>
                    <input type="text" name="ruc" value="<?= htmlspecialchars($empresa['ruc'] ?? '') ?>" maxlength="11" placeholder="20123456789">
                </div>
                <div class="form-group">
                    <label>Razón Social</label>
                    <input type="text" name="razon_social" value="<?= htmlspecialchars($empresa['razon_social'] ?? '') ?>" placeholder="MI EMPRESA S.A.C.">
                </div>
            </div>
            <div class="form-group">
                <label>Dirección Fiscal</label>
                <input type="text" name="direccion" value="<?= htmlspecialchars($empresa['direccion'] ?? '') ?>" placeholder="Av. Principal 123, Lima">
            </div>
            <div class="row">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" value="<?= htmlspecialchars($empresa['telefono'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($empresa['email'] ?? '') ?>">
                </div>
            </div>
            <button type="submit">💾 Guardar Datos Empresa</button>
        </form>
    </div>

    <!-- CREDENCIALES SOL -->
    <div class="card">
        <h2>🔐 Credenciales SOL</h2>
        <form method="POST" action="index.php?route=guardar_empresa">
            <div class="row">
                <div class="form-group">
                    <label>Usuario SOL</label>
                    <input type="text" name="sol_usuario" value="<?= htmlspecialchars($empresa['usuario_sol'] ?? '') ?>" placeholder="usuarioSOL">
                </div>
                <div class="form-group">
                    <label>Clave SOL</label>
                    <input type="password" name="sol_clave" value="<?= htmlspecialchars($empresa['clave_sol'] ?? '') ?>" placeholder="••••••••">
                </div>
            </div>
            <button type="submit">🔐 Guardar Credenciales SOL</button>
        </form>
    </div>

    <!-- CERTIFICADO DIGITAL -->
    <div class="card">
        <h2>📜 Certificado Digital (.pem)</h2>
        <?php if (!empty($empresa['certificado_path'])): ?>
            <div class="cert-info">
                ✅ Certificado cargado: <code><?= basename($empresa['certificado_path']) ?></code>
            </div>
        <?php else: ?>
            <p style="color:#999;">No hay certificado cargado.</p>
        <?php endif; ?>
        <form method="POST" action="index.php?route=subir_certificado" enctype="multipart/form-data" style="margin-top:15px;">
            <div class="form-group">
                <input type="file" name="certificado" accept=".pem" required>
            </div>
            <button type="submit" class="btn-success">📤 Subir Certificado</button>
        </form>
    </div>

    <!-- CORRELATIVOS -->
    <div class="card">
        <h2>🧾 Correlativos de Comprobantes</h2>
        <form method="POST" action="index.php?route=guardar_correlativos">
            <table>
                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Serie</th>
                        <th>Correlativo Actual</th>
                        <th>Nuevo Correlativo</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($series as $s): ?>
                    <tr>
                        <td>
                            <?php if (($s['tipo_comprobante'] ?? '') == '01'): ?>
                                <span class="badge badge-factura">FACTURA</span>
                            <?php else: ?>
                                <span class="badge badge-boleta">BOLETA</span>
                            <?php endif; ?>
                        </td>
                        <td><strong><?= htmlspecialchars($s['serie']) ?></strong></td>
                        <td><?= $s['correlativo'] ?></td>
                        <td>
                            <input type="number" name="series[<?= htmlspecialchars($s['serie']) ?>]" value="<?= $s['correlativo'] ?>" min="1" style="width:120px;">
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="submit" style="margin-top:15px;">📝 Guardar Correlativos</button>
        </form>
    </div>

</div>
</body>
</html>