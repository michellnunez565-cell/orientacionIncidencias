<?php
// views/seguimiento.php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../controllers/IncidentController.php';

$incidentController = new IncidentController();
$statusFilter = $_GET['status'] ?? null;
$search = $_GET['search'] ?? '';

$reports = $incidentController->getAllReports($search, $statusFilter);
$message = $_GET['msg'] ?? '';
$error = $_GET['err'] ?? '';
?>

<div class="card-header" style="margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary);">🔍 Seguimiento de Casos Disciplinarios</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Control y atención de expedientes, citas a padres de familia, cartas compromiso y resoluciones.
        </p>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Filtros de Estado -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="index.php" method="GET" class="form-grid" style="align-items: flex-end;">
        <input type="hidden" name="action" value="seguimiento">
        
        <div class="form-group" style="margin-bottom: 0;">
            <label for="search">Buscar Alumno o Código</label>
            <input type="text" id="search" name="search" class="form-control" placeholder="Nombre de alumno..." value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="status">Filtrar por Estado del Caso</label>
            <select id="status" name="status" class="form-control">
                <option value="">-- Todos los Estados --</option>
                <option value="Pendiente" <?= $statusFilter === 'Pendiente' ? 'selected' : '' ?>>Pendiente de Atención</option>
                <option value="Citatorio Enviado" <?= $statusFilter === 'Citatorio Enviado' ? 'selected' : '' ?>>Citatorio Enviado</option>
                <option value="Atendido en Orientación" <?= $statusFilter === 'Atendido en Orientación' ? 'selected' : '' ?>>Atendido en Orientación</option>
                <option value="Compromiso Firmado" <?= $statusFilter === 'Compromiso Firmado' ? 'selected' : '' ?>>Compromiso Firmado</option>
                <option value="Sanción Aplicada" <?= $statusFilter === 'Sanción Aplicada' ? 'selected' : '' ?>>Sanción Aplicada</option>
                <option value="Resuelto" <?= $statusFilter === 'Resuelto' ? 'selected' : '' ?>>Resuelto / Cerrado</option>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary">Filtrar</button>
            <a href="index.php?action=seguimiento" class="btn btn-outline">Limpiar</a>
        </div>
    </form>
</div>

<!-- Listado de Casos -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fecha Reporte</th>
                    <th>Estudiante</th>
                    <th>Curso / Sección</th>
                    <th>Gravedad</th>
                    <th>Estado Actual</th>
                    <th>Acciones de Seguimiento</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reports)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            No hay casos disciplinarios registrados con los filtros aplicados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($reports as $rep): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($rep['report_code']) ?></code></td>
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
                            <td style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <button onclick="openFollowupModal(<?= $rep['id'] ?>, '<?= htmlspecialchars(addslashes($rep['student_name'])) ?>', '<?= htmlspecialchars($rep['status']) ?>')" class="btn btn-primary btn-sm">
                                    ➕ Registrar Avance / Cita
                                </button>
                                <a href="index.php?action=reporte_estudiante&id=<?= $rep['id'] ?>" class="btn btn-outline btn-sm">
                                    🖨️ Ver Expediente
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: REGISTRAR SEGUIMIENTO DE CASO -->
<div class="modal-overlay" id="modalFollowup">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">📝 Actualizar Seguimiento de Caso</div>
            <button class="modal-close" onclick="closeModal('modalFollowup')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="index.php?action=guardar_seguimiento" method="POST">
                <input type="hidden" id="modal_report_id" name="report_id">

                <div class="form-group">
                    <label>Estudiante:</label>
                    <div id="modal_student_name" style="font-weight: 700; color: var(--primary); font-size: 1.05rem;"></div>
                </div>

                <div class="form-group">
                    <label for="new_status">Cambiar Estado del Caso *</label>
                    <select id="new_status" name="new_status" class="form-control" required>
                        <option value="Pendiente">Pendiente de Atención</option>
                        <option value="Citatorio Enviado">Citatorio Enviado a Padre/Tutor</option>
                        <option value="Atendido en Orientación">Atendido en Orientación</option>
                        <option value="Compromiso Firmado">Compromiso Firmado por Alumno/Padre</option>
                        <option value="Sanción Aplicada">Sanción / Medida Correctiva Aplicada</option>
                        <option value="Resuelto">Resuelto / Caso Cerrado</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="action_taken">Acción Realizada / Entrevista *</label>
                    <input type="text" id="action_taken" name="action_taken" class="form-control" placeholder="Ej. Entrevista individual con el alumno y llamada al tutor" required>
                </div>

                <div class="form-group" style="display: flex; align-items: center; gap: 0.5rem;">
                    <input type="checkbox" id="guardian_present" name="guardian_present" value="1" style="width: 18px; height: 18px;">
                    <label for="guardian_present" style="margin-bottom: 0; font-weight: normal;">Padre de familia o tutor estuvo presente</label>
                </div>

                <div class="form-group">
                    <label for="agreement_notes">Acuerdos y Compromisos Establecidos</label>
                    <textarea id="agreement_notes" name="agreement_notes" class="form-control" rows="3" placeholder="Detalle de acuerdos o compromisos aceptados por el alumno o tutor..."></textarea>
                </div>

                <div class="form-group">
                    <label for="next_date">Próxima Fecha de Seguimiento (Opcional)</label>
                    <input type="date" id="next_date" name="next_date" class="form-control">
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('modalFollowup')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Seguimiento</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openFollowupModal(reportId, studentName, currentStatus) {
    document.getElementById('modal_report_id').value = reportId;
    document.getElementById('modal_student_name').textContent = studentName;
    document.getElementById('new_status').value = currentStatus;
    openModal('modalFollowup');
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
