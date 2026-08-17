<?php
// views/crear_incidencia.php
require_once __DIR__ . '/header.php';
require_once __DIR__ . '/../controllers/StudentController.php';
require_once __DIR__ . '/../controllers/IncidentController.php';
require_once __DIR__ . '/../controllers/UserController.php';

$studentController = new StudentController();
$incidentController = new IncidentController();
$userController = new UserController();

$courses = $studentController->getAllCourses();
$students = $studentController->getAllStudents();
$teachers = $userController->getAllUsers();
$incidentTypes = $incidentController->getAllIncidentTypes(true);

$preselectedStudentId = $_GET['student_id'] ?? null;
$error = $_GET['err'] ?? '';
?>

<div class="card" style="max-width: 850px; margin: 0 auto;">
    <div class="card-header" style="display: flex; align-items: center; justify-content: space-between;">
        <div style="display: flex; align-items: center; gap: 1rem;">
            <img src="assets/img/logo.jpg" alt="Logo Colegio" style="height: 55px; width: 55px; object-fit: contain;">
            <div>
                <h2 class="card-title" style="font-size: 1.25rem; margin-bottom: 0.2rem;">📝 Reportar Nueva Incidencia Disciplinaria</h2>
                <p style="font-size: 0.85rem; color: var(--text-muted);">
                    Seleccione de la lista o <strong>escriba manualmente</strong> el nombre del estudiante y del maestro reportante.
                </p>
            </div>
        </div>
        <?php if ($isOrientadora): ?>
            <a href="index.php?action=catalogo_faltas" class="btn btn-outline btn-sm">
                ⚙️ Catálogo de Faltas
            </a>
        <?php endif; ?>
    </div>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <form action="index.php?action=guardar_incidencia" method="POST">
        
        <!-- SECCIÓN DE SELECCIÓN O ESCRITURA DE DATOS DE ALUMNO Y MAESTRO -->
        <div style="background: #f8fafc; padding: 1.25rem; border-radius: var(--radius-sm); border: 1px solid var(--border); margin-bottom: 1.5rem;">
            <h3 style="font-size: 1rem; color: var(--primary); margin-bottom: 1rem; border-bottom: 1px solid var(--border); padding-bottom: 0.5rem; display: flex; align-items: center; gap: 0.5rem;">
                <span>📌 1. Datos del Estudiante y Maestro (Seleccionar o Escribir)</span>
            </h3>

            <!-- SELECCIÓN DE GRADO -->
            <div class="form-group">
                <label for="filter_course_id">Grado / Sección / Modalidad *</label>
                <select id="filter_course_id" name="filter_course_id" class="form-control" onchange="filterStudentsByCourse()">
                    <option value="">-- Seleccionar Grado / Sección --</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>">
                            <?= htmlspecialchars($c['grade']) ?> - <?= htmlspecialchars($c['modality']) ?> (<?= htmlspecialchars($c['section_name']) ?>)
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- SELECCIÓN O ESCRITURA DE ESTUDIANTE -->
            <div class="form-grid">
                <div class="form-group">
                    <label for="student_id">Seleccionar Estudiante (Lista BD)</label>
                    <select id="student_id" name="student_id" class="form-control" onchange="clearCustomStudentInput()">
                        <option value="">-- Seleccionar de la BD --</option>
                        <?php foreach ($students as $st): ?>
                            <option value="<?= $st['id'] ?>" data-course-id="<?= $st['course_id'] ?>" <?= $preselectedStudentId == $st['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($st['full_name']) ?> [<?= htmlspecialchars($st['grade'] ?? '') ?> - <?= htmlspecialchars($st['section_name'] ?? '') ?>]
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="custom_student_name">O Escribir Nombre del Estudiante</label>
                    <input type="text" id="custom_student_name" name="custom_student_name" class="form-control" placeholder="Escriba el nombre completo del alumno..." oninput="clearSelectStudentInput()">
                </div>
            </div>

            <!-- SELECCIÓN O ESCRITURA DE MAESTRO -->
            <div class="form-grid" style="margin-top: 0.5rem;">
                <div class="form-group">
                    <label for="reporter_user_id">Seleccionar Maestro(a) (Lista BD)</label>
                    <select id="reporter_user_id" name="reporter_user_id" class="form-control" onchange="clearCustomTeacherInput()">
                        <?php foreach ($teachers as $t): ?>
                            <option value="<?= $t['id'] ?>" <?= $_SESSION['user_id'] == $t['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($t['full_name']) ?> (<?= ucfirst(htmlspecialchars($t['role'])) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="custom_reporter_name">O Escribir Nombre del Maestro(a)</label>
                    <input type="text" id="custom_reporter_name" name="custom_reporter_name" class="form-control" placeholder="Escriba el nombre del docente que reporta..." oninput="clearSelectTeacherInput()">
                </div>
            </div>
        </div>

        <!-- SECCIÓN DE SELECCIÓN DE FALTAS -->
        <div class="form-group" style="margin-top: 1.5rem;">
            <label style="font-size: 1rem; color: var(--primary); font-weight: 700;">
                📋 2. Seleccionar Faltas / Incidencias Ocurridas (Marque una o varias) *
            </label>
            <p style="font-size: 0.8rem; color: var(--text-muted); margin-bottom: 0.75rem;">
                Seleccione las faltas correspondientes registradas en el catálogo de Orientación:
            </p>

            <div class="incident-checklist">
                <?php foreach ($incidentTypes as $inc): ?>
                    <label class="checklist-item">
                        <input type="checkbox" name="incident_type_ids[]" value="<?= $inc['id'] ?>">
                        <div class="checklist-info">
                            <div class="checklist-title"><?= htmlspecialchars($inc['name']) ?></div>
                            <div class="checklist-desc">
                                Categoría: <?= htmlspecialchars($inc['category']) ?> |
                                <span class="badge badge-<?= strtolower(str_replace(' ', '-', $inc['severity'])) ?>">
                                    <?= htmlspecialchars($inc['severity']) ?>
                                </span>
                            </div>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Gravedad y Observaciones -->
        <div class="form-grid" style="margin-top: 1.5rem;">
            <div class="form-group">
                <label for="severity_level">Clasificación de Gravedad</label>
                <select id="severity_level" name="severity_level" class="form-control">
                    <option value="">-- Determinada Automáticamente por Faltas --</option>
                    <option value="Leve">Falta Leve</option>
                    <option value="Grave">Falta Grave</option>
                    <option value="Muy Grave">Falta Muy Grave</option>
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="observations">Observaciones Adicionales del Docente / Orientación</label>
            <textarea id="observations" name="observations" class="form-control" rows="4" placeholder="Describa el contexto o notas sobre lo ocurrido en el aula..."></textarea>
        </div>

        <div style="display: flex; justify-content: flex-end; gap: 0.75rem; margin-top: 1.5rem;">
            <a href="index.php?action=dashboard" class="btn btn-outline">Cancelar</a>
            <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem; font-size: 1rem;">
                🚀 Guardar y Registrar Incidencia
            </button>
        </div>
    </form>
</div>

<script>
function filterStudentsByCourse() {
    const courseId = document.getElementById('filter_course_id').value;
    const studentSelect = document.getElementById('student_id');
    const options = studentSelect.querySelectorAll('option');

    options.forEach(opt => {
        if (!opt.value) return;
        const optCourseId = opt.getAttribute('data-course-id');
        if (!courseId || optCourseId === courseId) {
            opt.style.display = '';
        } else {
            opt.style.display = 'none';
        }
    });
}

function clearCustomStudentInput() {
    const sel = document.getElementById('student_id');
    if (sel.value) {
        document.getElementById('custom_student_name').value = '';
    }
}

function clearSelectStudentInput() {
    const inp = document.getElementById('custom_student_name');
    if (inp.value.trim() !== '') {
        document.getElementById('student_id').value = '';
    }
}

function clearCustomTeacherInput() {
    const sel = document.getElementById('reporter_user_id');
    if (sel.value) {
        document.getElementById('custom_reporter_name').value = '';
    }
}

function clearSelectTeacherInput() {
    const inp = document.getElementById('custom_reporter_name');
    if (inp.value.trim() !== '') {
        document.getElementById('reporter_user_id').value = '';
    }
}
</script>

<?php require_once __DIR__ . '/footer.php'; ?>
