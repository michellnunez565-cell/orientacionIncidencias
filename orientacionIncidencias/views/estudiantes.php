<?php
// views/estudiantes.php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../controllers/StudentController.php';

$studentController = new StudentController();

$search = $_GET['search'] ?? '';
$courseId = $_GET['course_id'] ?? null;

$students = $studentController->getAllStudents($search, $courseId);
$courses = $studentController->getAllCourses();
$message = $_GET['msg'] ?? '';
$error = $_GET['err'] ?? '';
?>

<div class="card-header" style="margin-bottom: 1.5rem;">
    <div>
        <h2 style="font-size: 1.35rem; font-weight: 800; color: var(--primary);">👨‍🎓 Expediente de Estudiantes</h2>
        <p style="font-size: 0.85rem; color: var(--text-muted);">
            Listado oficial de alumnos por curso, sección y modalidad.
        </p>
    </div>
    <div style="display: flex; gap: 0.75rem; flex-wrap: wrap;">
        <button onclick="openModal('modalImport')" class="btn btn-secondary">
            📥 Importar / Actualizar con Matrícula
        </button>
        <button onclick="openModal('modalAddStudent')" class="btn btn-primary">
            ➕ Registrar Estudiante Individual
        </button>
    </div>
</div>

<?php if (!empty($message)): ?>
    <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<!-- Filtros de Búsqueda -->
<div class="card" style="margin-bottom: 1.5rem;">
    <form action="index.php" method="GET" class="form-grid" style="align-items: flex-end;">
        <input type="hidden" name="action" value="estudiantes">
        
        <div class="form-group" style="margin-bottom: 0;">
            <label for="search">Buscar por Nombre o Identidad</label>
            <input type="text" id="search" name="search" class="form-control" placeholder="Ej. Juan Pérez o 0501-2005-..." value="<?= htmlspecialchars($search) ?>">
        </div>

        <div class="form-group" style="margin-bottom: 0;">
            <label for="course_id">Filtrar por Curso y Modalidad</label>
            <select id="course_id" name="course_id" class="form-control">
                <option value="">-- Todos los Cursos y Modalidades --</option>
                <?php foreach ($courses as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= $courseId == $c['id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($c['grade']) ?> - <?= htmlspecialchars($c['modality']) ?> (<?= htmlspecialchars($c['section_name']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="display: flex; gap: 0.5rem;">
            <button type="submit" class="btn btn-primary">🔎 Filtrar</button>
            <a href="index.php?action=estudiantes" class="btn btn-outline">Limpiar</a>
        </div>
    </form>
</div>

<!-- Tabla de Estudiantes -->
<div class="card">
    <div class="table-responsive">
        <table class="table">
            <thead>
                <tr>
                    <th>Código</th>
                    <th>No. Identidad / RNV</th>
                    <th>Nombre Completo</th>
                    <th>Grado y Modalidad</th>
                    <th>Sección</th>
                    <th>Padre / Tutor</th>
                    <th>Teléfono</th>
                    <th>Incidencias</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($students)): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; color: var(--text-muted); padding: 2rem;">
                            No se encontraron estudiantes registrados con los criterios seleccionados.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($students as $st): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($st['student_code'] ?? 'EST') ?></code></td>
                            <td><?= htmlspecialchars($st['identity_num'] ?? 'N/A') ?></td>
                            <td><strong><?= htmlspecialchars($st['full_name']) ?></strong></td>
                            <td><?= htmlspecialchars($st['grade'] ?? 'Sin asignar') ?> - <?= htmlspecialchars($st['modality'] ?? '') ?></td>
                            <td><span class="badge badge-atendido"><?= htmlspecialchars($st['section_name'] ?? 'N/A') ?></span></td>
                            <td><?= htmlspecialchars($st['guardian_name'] ?? 'No especificado') ?></td>
                            <td><?= htmlspecialchars($st['guardian_phone'] ?? 'N/A') ?></td>
                            <td>
                                <?php if ($st['total_incidents'] > 0): ?>
                                    <span class="badge badge-grave"><?= $st['total_incidents'] ?> incidencias</span>
                                <?php else: ?>
                                    <span class="badge badge-resuelto">Limpio</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <a href="index.php?action=crear_incidencia&student_id=<?= $st['id'] ?>" class="btn btn-secondary btn-sm">
                                    📝 Reportar
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- MODAL: Importar Matrícula -->
<div class="modal-overlay" id="modalImport">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">📥 Sincronizar Base de Datos con Matrícula</div>
            <button class="modal-close" onclick="closeModal('modalImport')">&times;</button>
        </div>
        <div class="modal-body">
            <p style="font-size: 0.875rem; color: var(--text-muted); margin-bottom: 1rem;">
                Suba un archivo en formato CSV exportado del Sistema de Matrícula para actualizar de forma masiva los estudiantes y sus secciones.
            </p>

            <div style="background: #f1f5f9; padding: 0.75rem; border-radius: 8px; font-size: 0.8rem; margin-bottom: 1rem;">
                <strong>Formato del archivo CSV (Columnas requeridas):</strong><br>
                1. Identidad | 2. Nombre Completo | 3. Grado (7mo..12mo) | 4. Modalidad | 5. Sección | 6. Padre/Tutor | 7. Teléfono
            </div>

            <form action="index.php?action=importar_matricula" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="csv_file">Seleccionar Archivo CSV de Matrícula</label>
                    <input type="file" id="csv_file" name="csv_file" class="form-control" accept=".csv, .txt" required>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('modalImport')">Cancelar</button>
                    <button type="submit" class="btn btn-secondary">⚡ Procesar e Importar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL: Agregar Estudiante Individual -->
<div class="modal-overlay" id="modalAddStudent">
    <div class="modal-container">
        <div class="modal-header">
            <div class="modal-title">➕ Registrar Estudiante Individual</div>
            <button class="modal-close" onclick="closeModal('modalAddStudent')">&times;</button>
        </div>
        <div class="modal-body">
            <form action="index.php?action=guardar_estudiante" method="POST">
                <div class="form-group">
                    <label for="full_name">Nombre Completo del Estudiante *</label>
                    <input type="text" id="full_name" name="full_name" class="form-control" placeholder="Ej. Carlos Eduardo Mejia" required>
                </div>

                <div class="form-group">
                    <label for="identity_num">No. Identidad / RNV</label>
                    <input type="text" id="identity_num" name="identity_num" class="form-control" placeholder="Ej. 0501-2008-12345">
                </div>

                <div class="form-group">
                    <label for="course_id_modal">Curso, Modalidad y Sección *</label>
                    <select id="course_id_modal" name="course_id" class="form-control" required>
                        <option value="">-- Seleccionar Sección --</option>
                        <?php foreach ($courses as $c): ?>
                            <option value="<?= $c['id'] ?>">
                                <?= htmlspecialchars($c['grade']) ?> - <?= htmlspecialchars($c['modality']) ?> (<?= htmlspecialchars($c['section_name']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="guardian_name">Padre de Familia / Tutor</label>
                        <input type="text" id="guardian_name" name="guardian_name" class="form-control" placeholder="Nombre del responsable">
                    </div>
                    <div class="form-group">
                        <label for="guardian_phone">Teléfono de Contacto</label>
                        <input type="text" id="guardian_phone" name="guardian_phone" class="form-control" placeholder="Ej. 9988-7766">
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 0.5rem; margin-top: 1.5rem;">
                    <button type="button" class="btn btn-outline" onclick="closeModal('modalAddStudent')">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar Estudiante</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/footer.php'; ?>
