<?php
// views/login.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$error = $error ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión | CEMG Técnico Dr. Jorge Fidel Durón</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f2942 0%, #1e3a8a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 1rem;
        }
        .login-card {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 420px;
            padding: 2.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }
        .login-header {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .login-logo-img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin-bottom: 0.75rem;
        }
        .login-title {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f2942;
        }
        .login-subtitle {
            font-size: 0.8rem;
            color: #64748b;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body>
<div class="login-card">
    <div class="login-header">
        <img src="assets/img/logo.jpg" alt="Logo C.E.M.G. Técnico Dr. Jorge Fidel Durón" class="login-logo-img">
        <div class="login-title">Acceso al Sistema</div>
        <div class="login-subtitle">C.E.M.G. TÉCNICO "DR. JORGE FIDEL DURÓN"<br>Departamento de Orientación</div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="index.php?action=do_login" method="POST">
        <div class="form-group">
            <label for="username">Nombre de Usuario</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="Ingrese su usuario" required autofocus>
        </div>

        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; padding: 0.75rem;">
            🔑 Iniciar Sesión
        </button>
    </form>

    <div style="margin-top: 1.5rem; text-align: center; font-size: 0.775rem; color: #94a3b8;">
        Acceso exclusivo para Orientadoras y Maestros autorizados.
    </div>
</div>
</body>
</html>
