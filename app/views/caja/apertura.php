<?php require_once '../app/views/includes/header.php'; ?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-lg border-success">
                <div class="card-header bg-success text-white text-center">
                    <h4>🌅 Apertura de Caja</h4>
                </div>
                <div class="card-body">
                    <p>Hola <strong><?php echo $_SESSION['user_nombre']; ?></strong>, antes de vender necesitas abrir tu caja.</p>
                    
                    <form action="index.php?route=guardar_apertura" method="POST">
                        <div class="mb-4">
                            <label class="form-label">Monto Inicial (Sencillo/Cambio)</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text">S/</span>
                                <input type="number" step="0.01" name="monto_inicial" class="form-control" value="0.00" required>
                            </div>
                            <div class="form-text">Cuenta el dinero físico que tienes en el cajón.</div>
                        </div>
                        <button type="submit" class="btn btn-success w-100 btn-lg">Abrir Caja y Empezar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>