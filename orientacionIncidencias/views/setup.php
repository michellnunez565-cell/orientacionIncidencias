<?php
// views/setup.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$message = $message ?? '';
$error = $error ?? '';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración Inicial | C.E.M.G. Técnico Dr. Jorge Fidel Durón</title>
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
        .setup-card {
            background: white;
            border-radius: 16px;
            width: 100%;
            max-width: 480px;
            padding: 2.5rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.3);
        }
        .setup-logo {
            text-align: center;
            margin-bottom: 1.5rem;
        }
        .setup-logo-img {
            width: 90px;
            height: 90px;
            object-fit: contain;
            margin-bottom: 0.5rem;
        }
        .setup-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f2942;
            text-align: center;
        }
        .setup-subtitle {
            font-size: 0.85rem;
            color: #64748b;
            text-align: center;
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>
<div class="setup-card">
    <div class="setup-logo">
        <img src="assets/img/logo.jpg" alt="Logo C.E.M.G. Técnico Dr. Jorge Fidel Durón" class="setup-logo-img">
        <div class="setup-title">Configuración Inicial</div>
        <div class="setup-subtitle">C.E.M.G. TÉCNICO "DR. JORGE FIDEL DURÓN"<br>Departamento de Orientación</div>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <p style="font-size: 0.875rem; color: #475569; margin-bottom: 1.25rem;">
        Bienvenido(a). Configure la cuenta principal de la <strong>Orientadora</strong> que tendrá acceso total a la administración de expedientes, catálogo de incidencias y reportes.
    </p>

    <form action="index.php?action=do_setup" method="POST">
        <div class="form-group">
            <label for="fullName">Nombre Completo de la Orientadora</label>
            <input type="text" id="fullName" name="full_name" class="form-control" placeholder="Ej. Licda. Maria Carmen Lopez" required>
        </div>

        <div class="form-group">
            <label for="username">Nombre de Usuario para Iniciar Sesión</label>
            <input type="text" id="username" name="username" class="form-control" placeholder="Ej. orientadora" required>
        </div>

        <div class="form-group">
            <label for="password">Contraseña Segura</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
        </div>

        <div class="form-group">
            <label for="email">Correo Electrónico (Opcional)</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="orientacion@colegio.edu.hn">
        </div>

        <div class="form-group">
            <label for="phone">Teléfono de Contacto (Opcional)</label>
            <input type="text" id="phone" name="phone" class="form-control" placeholder="Ej. +504 9999-8888">
        </div>

        <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem; padding: 0.8rem;">
            🚀 Guardar y Comenzar a Usar el Sistema
        </button>
    </form>
</div>
</body>
</html>
