<?php
// views/header.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$isOrientadora = isset($_SESSION['role']) && $_SESSION['role'] === 'orientadora';
$fullName = $_SESSION['full_name'] ?? 'Usuario';
$roleName = $isOrientadora ? 'Orientadora' : 'Docente / Maestro';
$currentAction = $_GET['action'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Registro de Incidencias Disciplinarias | C.E.M.G. Técnico Dr. Jorge Fidel Durón</title>
    <!-- Metadatos de Aplicación Android / PWA -->
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#0c3866">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="assets/img/logo.png">

    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/print.css" media="print">
    <script src="assets/js/app.js" defer></script>
</head>
<body>
<div class="app-container">
    <!-- Sidebar / Menú Lateral con Logo Oficial -->
    <aside class="sidebar">
        <div class="sidebar-header">
            <img src="assets/img/logo.jpg" alt="Logo Colegio" style="width: 46px; height: 46px; object-fit: contain; border-radius: 50%; background: white; padding: 2px;">
            <div class="sidebar-title">
                <h1>CEMG Técnico<br>Jorge Fidel Durón</h1>
                <p>Orientación Educativa</p>
            </div>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-section-title">Menú Principal</div>
            <a href="index.php?action=dashboard" class="nav-item <?= $currentAction === 'dashboard' ? 'active' : '' ?>">
                <span>📊</span> Dashboard / Inicio
            </a>
            
            <a href="index.php?action=crear_incidencia" class="nav-item <?= $currentAction === 'crear_incidencia' ? 'active' : '' ?>">
                <span>📝</span> Reportar Incidencia
            </a>

            <?php if ($isOrientadora): ?>
                <a href="index.php?action=estudiantes" class="nav-item <?= $currentAction === 'estudiantes' ? 'active' : '' ?>">
                    <span>👨‍🎓</span> Estudiantes / Expedientes
                </a>

                <a href="index.php?action=seguimiento" class="nav-item <?= $currentAction === 'seguimiento' ? 'active' : '' ?>">
                    <span>🔍</span> Seguimiento de Casos
                </a>

                <a href="index.php?action=catalogo_faltas" class="nav-item <?= $currentAction === 'catalogo_faltas' ? 'active' : '' ?>">
                    <span>⚙️</span> Catálogo de Incidencias
                </a>

                <a href="index.php?action=reportes" class="nav-item <?= $currentAction === 'reportes' ? 'active' : '' ?>">
                    <span>🖨️</span> Reportes por Sección
                </a>

                <div class="nav-section-title">Administración</div>
                <a href="index.php?action=usuarios" class="nav-item <?= $currentAction === 'usuarios' ? 'active' : '' ?>">
                    <span>👥</span> Gestión de Maestros
                </a>
            <?php else: ?>
                <a href="index.php?action=mis_reportes" class="nav-item <?= $currentAction === 'mis_reportes' ? 'active' : '' ?>">
                    <span>📋</span> Mis Reportes Enviados
                </a>
            <?php endif; ?>

            <div class="nav-section-title">Información</div>
            <a href="javascript:void(0)" onclick="openModal('modalCopyright')" class="nav-item">
                <span>©️</span> Derechos de Autor
            </a>
        </nav>

        <div class="sidebar-footer">
            <div class="user-badge">
                <div class="user-info">
                    <span class="user-name"><?= htmlspecialchars($fullName) ?></span>
                    <span class="user-role"><?= $roleName ?></span>
                </div>
                <a href="index.php?action=logout" class="btn-logout" title="Cerrar Sesión">Salir</a>
            </div>
        </div>
    </aside>

    <!-- Main Wrapper -->
    <div class="main-wrapper">
        <header class="top-bar">
            <div class="top-title" style="display: flex; align-items: center; gap: 0.75rem;">
                <img src="assets/img/logo.jpg" alt="Logo" style="height: 32px;">
                <span>Sistema de Registro de Incidencias</span>
            </div>
            <div style="display: flex; align-items: center; gap: 0.5rem;">
                <button id="btnInstallPwa" class="btn btn-secondary btn-sm" style="display: none;" title="Instalar Aplicación en Celular Android">
                    📱 Instalar App Android
                </button>
                <button onclick="openModal('modalCopyright')" class="btn btn-outline btn-sm" style="font-size: 0.75rem;">
                    ©️ Derechos de Autor
                </button>
                <span class="badge badge-atendido"><?= date('d/m/Y') ?></span>
            </div>
        </header>

        <!-- MODAL DERECHOS DE AUTOR -->
        <div class="modal-overlay" id="modalCopyright">
            <div class="modal-container">
                <div class="modal-header">
                    <div class="modal-title">©️ Derechos de Autor y Ficha Técnica</div>
                    <button class="modal-close" onclick="closeModal('modalCopyright')">&times;</button>
                </div>
                <div class="modal-body" style="text-align: center;">
                    <img src="assets/img/logo.jpg" alt="Logo Colegio" style="height: 80px; width: 80px; object-fit: contain; margin-bottom: 0.75rem;">
                    <h3 style="color: var(--primary); font-size: 1.15rem; font-weight: 800;">C.E.M.G. Técnico "Dr. Jorge Fidel Durón"</h3>
                    <p style="color: var(--text-muted); font-size: 0.85rem; margin-bottom: 1rem;">San Francisco de Yojoa, Cortés — Departamento de Orientación</p>

                    <div style="background: #f8fafc; border: 1px solid var(--border); padding: 1rem; border-radius: 8px; text-align: left; font-size: 0.9rem; margin-bottom: 1rem;">
                        <div style="margin-bottom: 0.5rem;">
                            <strong>💻 Autora / Creadora del Sistema:</strong><br>
                            <span style="color: var(--primary); font-weight: 800; font-size: 1rem;">Geraldina Núñez</span>
                        </div>
                        <div style="margin-bottom: 0.5rem;">
                            <strong>🎓 Grado y Modalidad:</strong><br>
                            Duodécimo Grado en Informática (12º BTP)
                        </div>
                        <div style="margin-bottom: 0.5rem;">
                            <strong>📅 Año Lectivo:</strong> 2026
                        </div>
                        <hr style="border: 0; border-top: 1px solid var(--border); margin: 0.75rem 0;">
                        <div>
                            <strong>⚙️ Lenguajes de Programación y Tecnologías Utilizadas:</strong>
                            <ul style="margin-top: 0.35rem; padding-left: 1.25rem; color: #334155; line-height: 1.6;">
                                <li><strong>PHP 8 (Backend Principal)</strong>: Procesamiento de lógica de negocio, controladores y autenticación de usuarios.</li>
                                <li><strong>HTML5 & CSS3 (Frontend)</strong>: Maquetación web moderna y diseño con colores institucionales (Azul, Amarillo y Blanco).</li>
                                <li><strong>JavaScript ES6+ (Cliente)</strong>: Búsqueda dinámica en tiempo real, modales e interacciones de usuario.</li>
                                <li><strong>SQLite 3 / PDO</strong>: Motor de Base de Datos relacional embebido.</li>
                                <li><strong>Progressive Web App (PWA)</strong>: Service Worker y Web Manifest para instalación directa como App en teléfonos celulares Android.</li>
                            </ul>
                        </div>
                    </div>

                    <div style="font-size: 0.775rem; color: #64748b;">
                        Todos los derechos reservados © 2026. Proyecto de graduación / Avance 4 presentado para la defensa técnica.
                    </div>

                    <div style="margin-top: 1.25rem;">
                        <button class="btn btn-primary" onclick="closeModal('modalCopyright')">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>

        <main class="main-content">
