<?php
// views/dashboard_docente.php
require_once __DIR__ . '/header.php';

$userId = $_SESSION['user_id'];
$db = Database::getInstance()->getConnection();

// Mis reportes emitidos
$stmt = $db->prepare("
    SELECT r.*, s.full_name as student_name, c.grade, c.modality, c.section_name
    FROM incident_reports r
    JOIN students s ON r.student_id = s.id
    LEFT JOIN courses c ON s.course_id = c.id
    WHERE r.reporter_user_id = ?
    ORDER BY r.created_at DESC
");
$stmt->execute([$userId]);
$myReports = $stmt->fetchAll();
?>

<div class="card" style="background: linear-gradient(135deg, #0f2942 0%, #1e3a8a 100%); color: white; margin-bottom: 1.5rem;">
    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
        <div>
            <h2 style="font-size: 1.35rem; font-weight: 800;">Bienvenido(a), Maestro(a) <?= htmlspecialchars($_SESSION['full_name']) ?></h2>
            <p style="color: #cbd5e1; font-size: 0.9rem; margin-top: 0.25rem;">
                Portal de Reportes Disciplinarios. Registre las incidencias observadas en el aula de clases.
            </p>
        </div>
        <a href="index.php?action=crear_incidencia" class="btn btn-secondary" style="padding: 0.75rem 1.25rem; font-size: 1rem;">
            📝 Reportar Nueva Incidencia
        </a>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">📋 Mis Reportes Enviados a Orientación</div>
        <input type="text" class="form-control live-search-input" data-table="table-my-reports" placeholder="🔎 Buscar por alumno..." style="max-width: 280px;">
    </div>

    <div class="table-responsive">
        <table class="table" id="table-my-reports">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fecha</th>
                    <th>Estudiante</th>
                    <th>Curso / Sección</th>
                    <th>Gravedad</th>
                    <th>Estado en Orientación</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($myReports)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            Usted aún no ha registrado ningún reporte disciplinario.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($myReports as $rep): ?>
                        <tr>
                            <td><strong><?= htmlspecialchars($rep['report_code']) ?></strong></td>
                            <td><?= date('d/m/Y', strtotime($rep['report_date'])) ?></td>
                            <td><strong><?= htmlspecialchars($rep['student_name']) ?></strong></td>
                            <td>
                                <?= htmlspecialchars($rep['grade'] ?? '') ?> 
                                <?= htmlspecialchars($rep['modality'] ?? '') ?> 
                                (<?= htmlspecialchars($rep['section_name'] ?? '') ?>)
                            </td>
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
                                <a href="index.php?action=reporte_estudiante&id=<?= $rep['id'] ?>" class="btn btn-outline btn-sm">
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
