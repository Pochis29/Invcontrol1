<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ingresar – InvControl</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background: linear-gradient(135deg, #1F4E79 0%, #2E75B6 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-card { background: #fff; border-radius: 16px; padding: 2.5rem; width: 100%; max-width: 400px; box-shadow: 0 20px 60px rgba(0,0,0,.3); }
        .login-card .brand { text-align: center; margin-bottom: 2rem; }
        .login-card .brand h2 { color: #1F4E79; font-weight: 800; }
        .login-card .brand p { color: #6c757d; font-size: .9rem; }
        .form-control:focus { border-color: #2E75B6; box-shadow: 0 0 0 .2rem rgba(46,117,182,.25); }
        .btn-login { background: #1F4E79; border: none; padding: .75rem; font-weight: 600; }
        .btn-login:hover { background: #2E75B6; }
    </style>
</head>
<body>
<div class="login-card">
    <div class="brand">
        <i class="bi bi-boxes" style="font-size:3rem; color:#1F4E79"></i>
        <h2>InvControl</h2>
        <p>Sistema Web de Inventarios</p>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger py-2"><i class="bi bi-exclamation-triangle-fill"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="<?= APP_URL ?>/?page=auth&action=login" novalidate>
        <div class="mb-3">
            <label class="form-label fw-semibold">Correo electrónico</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                <input type="email" name="email" class="form-control" placeholder="usuario@correo.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required autofocus>
            </div>
        </div>
        <div class="mb-4">
            <label class="form-label fw-semibold">Contraseña</label>
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                <input type="password" name="password" class="form-control" placeholder="••••••••" required>
            </div>
        </div>
        <button type="submit" class="btn btn-primary btn-login w-100 text-white">
            <i class="bi bi-box-arrow-in-right"></i> Ingresar
        </button>
    </form>
    <p class="text-center text-muted mt-3 mb-0" style="font-size:.8rem">
        ¿Problemas para ingresar? Contacte al administrador.
    </p>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
