<?php
// views/reporte_seccion.php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../controllers/StudentController.php';
require_once __DIR__ . '/../controllers/ReportController.php';

$studentController = new StudentController();
$reportController = new ReportController();

$courses = $studentController->getAllCourses();
$courseId = $_GET['course_id'] ?? ($courses[0]['id'] ?? null);
$startDate = $_GET['start_date'] ?? '';
$endDate = $_GET['end_date'] ?? '';

$sectionReport = $courseId ? $reportController->getSectionReport($courseId, $startDate, $endDate) : null;
?>

<!-- Filtros de Sección (Oculto al Imprimir) -->
<div class="card no-print" style="margin-bottom: 1.5rem;">
    <div class="card-header">
        <div class="card-title">🖨️ Generación de Reportes por Sección / Grado</div>
        <button onclick="window.print()" class="btn btn-secondary">
            🖨️ Imprimir Reporte Consolidado
        </button>
    </div>

    <form action="index.php" method="GET" class="form-grid" style="align-items: flex-end;">
        <input type="hidden" name="action" value="reportes">

        <div class="form-group" style="margin-bottom: 0;">
            <label for="course_id">Seleccionar Curso, Modalidad y Sección *</label>
            <select id="course_id" name="course_id" class="form-control" required>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['grade']) ?> - <?= htmlspecialchars($c['modality']) ?> (<?= htmlspecialchars($c['section_name']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="start_date">Fecha Inicial (Opcional)</label>
            <input type="date" id="start_date" name="start_date" class="form-control" value="<?= htmlspecialchars($startDate) ?>">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="end_date">Fecha Final (Opcional)</label>
            <input type="date" id="end_date" name="end_date" class="form-control" value="<?= htmlspecialchars($endDate) ?>">
        </div>

        <div>
            <button type="submit" class="btn btn-primary">⚡ Generar Reporte</button>
        </div>
    </form>
</div>

<!-- FORMATO DE IMPRESIÓN POR SECCIÓN CON LOGO OFICIAL -->
<?php if ($sectionReport): ?>
    <?php $course = $sectionReport['course']; $reports = $sectionReport['reports']; $stats = $sectionReport['stats']; ?>
    
    <div class="print-page card" style="background: white;">
        <div class="print-header" style="display: flex; align-items: center; justify-content: center; gap: 1.5rem; text-align: center; border-bottom: 2px solid #000; padding-bottom: 10px; margin-bottom: 15px;">
            <img src="assets/img/logo.jpg" alt="Logo C.E.M.G. Técnico Dr. Jorge Fidel Durón" style="height: 85px; width: 85px; object-fit: contain;">
            <div>
                <h1 style="font-size: 14pt; font-weight: bold; text-transform: uppercase; margin: 0;">C.E.M.G. TÉCNICO "DR. JORGE FIDEL DURÓN"</h1>
                <h2 style="font-size: 12pt; font-weight: bold; margin: 2px 0;">Departamento de Orientación</h2>
                <h3 style="font-size: 12pt; font-weight: bold; text-transform: uppercase; margin-top: 4px;">INFORME DISCIPLINARIO POR SECCIÓN Y MODALIDAD</h3>
            </div>
        </div>

        <div style="margin-bottom: 1.25rem; font-size: 11pt; border-bottom: 1px solid #000; padding-bottom: 8px;">
            <strong>Grado / Curso:</strong> <?= htmlspecialchars($course['grade']) ?> &nbsp;|&nbsp;
            <strong>Modalidad:</strong> <?= htmlspecialchars($course['modality']) ?> &nbsp;|&nbsp;
            <strong>Sección:</strong> <?= htmlspecialchars($course['section_name']) ?><br>
            <strong>Fecha de Emisión:</strong> <?= date('d/m/Y') ?>
            <?php if (!empty($startDate) || !empty($endDate)): ?>
                &nbsp;|&nbsp; <strong>Rango:</strong> <?= htmlspecialchars($startDate) ?> al <?= htmlspecialchars($endDate) ?>
            <?php endif; ?>
        </div>

        <!-- Métricas Rápidas de la Sección -->
        <div style="display: flex; gap: 1rem; margin-bottom: 1.5rem; justify-content: space-around; background: #f8fafc; padding: 10px; border: 1px solid #e2e8f0; border-radius: 8px;">
            <div>Total Incidencias: <strong><?= $stats['total'] ?></strong></div>
            <div>Leves: <strong style="color: var(--success);"><?= $stats['leve'] ?></strong></div>
            <div>Graves: <strong style="color: var(--warning);"><?= $stats['grave'] ?></strong></div>
            <div>Muy Graves: <strong style="color: var(--danger);"><?= $stats['muy_grave'] ?></strong></div>
            <div>Casos Resueltos: <strong style="color: var(--success);"><?= $stats['resueltos'] ?></strong></div>
        </div>

        <div class="table-responsive">
            <table class="table" style="border: 1px solid #000;">
                <thead>
                    <tr style="background: #e2e8f0;">
                        <th style="border: 1px solid #000;">Código</th>
                        <th style="border: 1px solid #000;">Fecha</th>
                        <th style="border: 1px solid #000;">Nombre del Estudiante</th>
                        <th style="border: 1px solid #000;">Docente Reportante</th>
                        <th style="border: 1px solid #000;">Gravedad</th>
                        <th style="border: 1px solid #000;">Estado en Orientación</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($reports)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 1.5rem; border: 1px solid #000;">
                                No hay incidencias disciplinarias registradas para esta sección en el rango seleccionado.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($reports as $r): ?>
                            <tr>
                                <td style="border: 1px solid #000;"><code><?= htmlspecialchars($r['report_code']) ?></code></td>
                                <td style="border: 1px solid #000;"><?= date('d/m/Y', strtotime($r['report_date'])) ?></td>
                                <td style="border: 1px solid #000;"><strong><?= htmlspecialchars($r['student_name']) ?></strong></td>
                                <td style="border: 1px solid #000;"><?= htmlspecialchars($r['reporter_name']) ?></td>
                                <td style="border: 1px solid #000;"><?= htmlspecialchars($r['severity_level']) ?></td>
                                <td style="border: 1px solid #000;"><?= htmlspecialchars($r['status']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <div class="print-signatures" style="margin-top: 50px;">
            <div class="signature-line">
                Licda. Orientadora Educativa
            </div>
            <div class="signature-line">
                Dirección del Centro Educativo
            </div>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/footer.php'; ?>
