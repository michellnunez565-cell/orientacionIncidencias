<?php
// views/dashboard_orientadora.php
require_once __DIR__ . '/header.php';

// Cargar métricas
$db = Database::getInstance()->getConnection();

$totalEstudiantes = $db->query("SELECT COUNT(*) FROM students")->fetchColumn();
$totalReportes = $db->query("SELECT COUNT(*) FROM incident_reports")->fetchColumn();
$casosActivos = $db->query("SELECT COUNT(*) FROM incident_reports WHERE status != 'Resuelto'")->fetchColumn();
$casosResueltos = $db->query("SELECT COUNT(*) FROM incident_reports WHERE status = 'Resuelto'")->fetchColumn();

// Últimos 10 reportes
$stmtLatest = $db->query("
    SELECT r.*, s.full_name as student_name, c.grade, c.modality, c.section_name, u.full_name as reporter_name
    FROM incident_reports r
    JOIN students s ON r.student_id = s.id
    LEFT JOIN courses c ON s.course_id = c.id
    JOIN users u ON r.reporter_user_id = u.id
    ORDER BY r.created_at DESC
    LIMIT 10
");
$latestReports = $stmtLatest->fetchAll();
?>

<!-- Tarjetas de Métricas -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon primary">👨‍🎓</div>
        <div class="stat-details">
            <h3><?= $totalEstudiantes ?></h3>
            <p>Total Estudiantes</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon warning">📋</div>
        <div class="stat-details">
            <h3><?= $totalReportes ?></h3>
            <p>Incidencias Registradas</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon danger">🔍</div>
        <div class="stat-details">
            <h3><?= $casosActivos ?></h3>
            <p>Casos Activos / Pendientes</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon success">✅</div>
        <div class="stat-details">
            <h3><?= $casosResueltos ?></h3>
            <p>Casos Resueltos</p>
        </div>
    </div>
</div>

<!-- Accesos Rápidos -->
<div class="card">
    <div class="card-header">
        <div class="card-title">⚡ Accesos Rápidos</div>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <a href="index.php?action=crear_incidencia" class="btn btn-primary">
            <span>📝</span> Reportar Nueva Incidencia
        </a>
        <a href="index.php?action=estudiantes" class="btn btn-secondary">
            <span>👨‍🎓</span> Expedientes e Importar Matrícula
        </a>
        <a href="index.php?action=catalogo_faltas" class="btn btn-outline">
            <span>⚙️</span> Catálogo y Agregar Faltas
        </a>
        <a href="index.php?action=seguimiento" class="btn btn-outline">
            <span>🔍</span> Bitácora de Seguimiento
        </a>
        <a href="index.php?action=reportes" class="btn btn-outline">
            <span>🖨️</span> Reportes por Sección
        </a>
    </div>
</div>

<!-- Tabla de Reportes Recientes -->
<div class="card">
    <div class="card-header">
        <div class="card-title">📋 Incidencias Recientes</div>
        <input type="text" class="form-control live-search-input" data-table="table-recent" placeholder="🔎 Buscar por alumno o código..." style="max-width: 280px;">
    </div>

    <div class="table-responsive">
        <table class="table" id="table-recent">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fecha</th>
                    <th>Estudiante</th>
                    <th>Curso / Sección</th>
                    <th>Reportado Por</th>
                    <th>Gravedad</th>
                    <th>Estado</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($latestReports)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            No hay reportes disciplinarios registrados hasta el momento.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($latestReports as $rep): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($rep['report_code']) ?></strong></td>
                            <td><?= date('d/m/Y', strtotime($rep['report_date'])) ?></td>
                            <td><strong><?= htmlspecialchars($rep['student_name']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($rep['grade'] ?? '') ?> 
                                <?= htmlspecialchars($rep['modality'] ?? '') ?> 
                                (<?= htmlspecialchars($rep['section_name'] ?? '') ?>)
                            </td>
                            <td><?= htmlspecialchars($rep['reporter_name']) ?></td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $rep['severity_level'])) ?>">
                                    <?= htmlspecialchars($rep['severity_level']) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $rep['status'])) ?>">
                                    <?= htmlspecialchars($rep['status']) ?>
                                </span>
                            </td>
                            <td>
                                <a href="index.php?action=reporte_estudiante&id=<?= $rep['id'] ?>" class="btn btn-outline btn-sm" title="Ver Boleta / Imprimir">
                                    🖨️ Ver / Imprimir
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
