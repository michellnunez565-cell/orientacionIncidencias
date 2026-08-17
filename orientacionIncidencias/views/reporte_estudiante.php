<?php
// views/reporte_estudiante.php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../controllers/ReportController.php';

$reportId = $_GET['id'] ?? null;
$reportController = new ReportController();

$data = $reportController->getStudentIndividualReport($reportId);

if (!$data) {
    echo "<div class='alert alert-danger'>El reporte solicitado no existe o fue eliminado.</div>";
    require_once __DIR__ . '/footer.php';
    exit;
}

$report = $data['report'];
$allTypes = $data['all_incident_types'];
$checkedIds = $data['checked_ids'];
?>

<!-- Botones de Acción de Pantalla (Ocultos al Imprimir) -->
<div class="no-print" style="margin-bottom: 1.5rem; display: flex; justify-content: space-between; align-items: center; background: white; padding: 1rem 1.5rem; border-radius: var(--radius); border: 1px solid var(--border);">
    <div>
        <a href="index.php?action=dashboard" class="btn btn-outline btn-sm">⬅️ Regresar</a>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <button onclick="window.print()" class="btn btn-secondary" style="font-size: 1rem; padding: 0.6rem 1.25rem;">
            🖨️ Imprimir Reporte de Disciplina
        </button>
    </div>
</div>

<!-- VISTA OFICIAL DE IMPRESIÓN (HOJA OFICIAL REGISTRO DE DISCIPLINA CON LOGO BLANCO) -->
<div class="print-page card" style="background: white; border: 1px solid #cbd5e1; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.1);">
    
    <!-- ENCABEZADO INSTITUCIONAL CON EL LOGO OFICIAL (FONDO BLANCO) -->
    <div class="print-header" style="display: flex; align-items: center; justify-content: center; gap: 1.5rem; text-align: center; border-bottom: 2px solid #0c3866; padding-bottom: 12px; margin-bottom: 18px;">
        <img src="assets/img/logo.jpg" alt="Logo C.E.M.G. Técnico Dr. Jorge Fidel Durón" style="height: 95px; width: 95px; object-fit: contain;">
        <div>
            <h1 style="font-size: 14pt; font-weight: bold; text-transform: uppercase; margin: 0 0 2px 0; color: #0c3866;">C.E.M.G. TÉCNICO "DR. JORGE FIDEL DURÓN"</h1>
            <h2 style="font-size: 12pt; font-weight: bold; margin: 0 0 2px 0; color: #000;">Departamento de Orientación Educativa</h2>
            <p style="font-size: 9.5pt; font-style: italic; margin: 0;">San Francisco de Yojoa, Cortés</p>
            <h3 style="font-size: 13pt; font-weight: bold; text-transform: uppercase; margin-top: 6px; text-decoration: underline; color: #0c3866;">REPORTE DE DISCIPLINA</h3>
        </div>
    </div>

    <!-- DATOS DEL ALUMNO Y FECHA -->
    <div class="print-student-info">
        <div class="print-info-row">
            <div><span class="print-info-label">Nombre del alumno(a):</span> <u><?= htmlspecialchars($report['student_name']) ?></u></div>
            <div><span class="print-info-label">Fecha:</span> <u><?= date('d/m/Y', strtotime($report['report_date'])) ?></u></div>
        </div>

        <div class="print-info-row">
            <div>
                <span class="print-info-label">Curso / Modalidad:</span> 
                <u><?= htmlspecialchars($report['grade'] ?? '') ?> <?= htmlspecialchars($report['modality'] ?? '') ?></u>
            </div>
            <div>
                <span class="print-info-label">Sección:</span> 
                <u><?= htmlspecialchars($report['section_name'] ?? 'N/A') ?></u>
            </div>
            <div>
                <span class="print-info-label">Reportado por:</span> 
                <u><?= htmlspecialchars($report['reporter_name']) ?></u>
            </div>
        </div>
    </div>

    <!-- TABLA ESTRUCTURADA Y PROFESIONAL: SOLO MUESTRA LAS FALTAS COMETIDAS -->
    <div style="font-weight: bold; margin-top: 18px; margin-bottom: 8px; text-transform: uppercase; font-size: 11pt; color: #0c3866; border-bottom: 2px solid #0c3866; padding-bottom: 4px;">
        Falta(s) / Incidencia(s) Disciplinaria(s) Registrada(s):
    </div>

    <table style="width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 11pt;">
        <thead>
            <tr style="background: #f1f5f9; text-align: left;">
                <th style="border: 1px solid #000; padding: 7px 10px; width: 40px; text-align: center;">No.</th>
                <th style="border: 1px solid #000; padding: 7px 10px;">Falta / Incidencia Cometida</th>
                <th style="border: 1px solid #000; padding: 7px 10px; width: 160px;">Categoría</th>
                <th style="border: 1px solid #000; padding: 7px 10px; width: 130px; text-align: center;">Clasificación</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($report['items'])): ?>
                <tr>
                    <td colspan="4" style="border: 1px solid #000; padding: 12px; text-align: center; font-style: italic;">
                        Sin faltas registradas.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($report['items'] as $idx => $item): ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 8px 10px; text-align: center; font-weight: bold;"><?= $idx + 1 ?></td>
                        <td style="border: 1px solid #000; padding: 8px 10px;"><strong><?= htmlspecialchars($item['name']) ?></strong></td>
                        <td style="border: 1px solid #000; padding: 8px 10px;"><?= htmlspecialchars($item['category']) ?></td>
                        <td style="border: 1px solid #000; padding: 8px 10px; text-align: center; font-weight: bold;">
                            <?= htmlspecialchars($item['severity']) ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- OBSERVACIONES DEL DOCENTE -->
    <div class="print-observations">
        <h4>Observaciones Adicionales:</h4>
        <p style="font-size: 11pt; white-space: pre-wrap; margin: 0; min-height: 50px;">
            <?= !empty($report['observations']) ? htmlspecialchars($report['observations']) : 'Sin observaciones adicionales registradas.' ?>
        </p>
    </div>

    <!-- SECCIÓN DE SEGUIMIENTO (SI EXISTE) -->
    <?php if (!empty($report['followups'])): ?>
        <div class="no-print" style="margin-top: 1.5rem; background: #f8fafc; padding: 1rem; border-radius: 8px; border: 1px solid var(--border);">
            <h4 style="color: var(--primary); font-size: 0.95rem; margin-bottom: 0.5rem;">📜 Historial de Bitácora de Seguimiento:</h4>
            <?php foreach ($report['followups'] as $fol): ?>
                <div style="border-left: 3px solid var(--accent); padding-left: 0.75rem; margin-bottom: 0.5rem; font-size: 0.85rem;">
                    <strong><?= date('d/m/Y', strtotime($fol['followup_date'])) ?></strong> - <?= htmlspecialchars($fol['action_taken']) ?><br>
                    <span style="color: var(--text-muted);">Por: <?= htmlspecialchars($fol['user_name']) ?></span> |
                    <span>Padre presente: <?= $fol['guardian_present'] ? 'Sí' : 'No' ?></span>
                    <?php if (!empty($fol['agreement_notes'])): ?>
                        <div style="font-style: italic; color: #475569; margin-top: 2px;">Acuerdos: <?= htmlspecialchars($fol['agreement_notes']) ?></div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- FIRMAS CORRESPONDIENTES -->
    <div class="print-signatures" style="margin-top: 60px;">
        <div class="signature-line">
            Firma del Docente
        </div>
        <div class="signature-line">
            Firma de Orientación
        </div>
        <div class="signature-line">
            Firma del Padre / Tutor
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
