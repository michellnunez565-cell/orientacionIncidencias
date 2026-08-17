<?php
// index.php - Enrutador Principal del Sistema

require_once __DIR__ . '/controllers/AuthController.php';
require_once __DIR__ . '/controllers/StudentController.php';
require_once __DIR__ . '/controllers/IncidentController.php';
require_once __DIR__ . '/controllers/FollowupController.php';
require_once __DIR__ . '/controllers/UserController.php';

$auth = new AuthController();
$action = $_GET['action'] ?? 'dashboard';

// 1. Si no hay usuarios registrados en la BD, forzar el Asistente de Configuración Inicial (Setup)
if (!$auth->hasUsers()) {
    if ($action === 'do_setup') {
        $res = $auth->setupInitialAdmin(
            $_POST['username'] ?? '',
            $_POST['password'] ?? '',
            $_POST['full_name'] ?? '',
            $_POST['email'] ?? '',
            $_POST['phone'] ?? ''
        );
        if ($res['success']) {
            header("Location: index.php?action=dashboard");
            exit;
        } else {
            $error = $res['message'];
            require_once __DIR__ . '/views/setup.php';
            exit;
        }
    }
    require_once __DIR__ . '/views/setup.php';
    exit;
}

// 2. Acciones públicas (Login)
if ($action === 'login') {
    require_once __DIR__ . '/views/login.php';
    exit;
}

if ($action === 'do_login') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $res = $auth->login($username, $password);
    if ($res['success']) {
        header("Location: index.php?action=dashboard");
        exit;
    } else {
        $error = $res['message'];
        require_once __DIR__ . '/views/login.php';
        exit;
    }
}

if ($action === 'logout') {
    $auth->logout();
    header("Location: index.php?action=login");
    exit;
}

// 3. Proteger todas las rutas restantes que requieren sesión iniciada
AuthController::requireLogin();

// 4. Enrutamiento de acciones autenticadas
switch ($action) {
    case 'dashboard':
        if (AuthController::isOrientadora()) {
            require_once __DIR__ . '/views/dashboard_orientadora.php';
        } else {
            require_once __DIR__ . '/views/dashboard_docente.php';
        }
        break;

    // --- ACCIONES DE ESTUDIANTES ---
    case 'estudiantes':
        AuthController::requireOrientadora();
        require_once __DIR__ . '/views/estudiantes.php';
        break;

    case 'guardar_estudiante':
        AuthController::requireOrientadora();
        $studentController = new StudentController();
        $res = $studentController->addStudent($_POST);
        $param = $res['success'] ? 'msg=' . urlencode($res['message']) : 'err=' . urlencode($res['message']);
        header("Location: index.php?action=estudiantes&$param");
        exit;

    case 'importar_matricula':
        AuthController::requireOrientadora();
        if (isset($_FILES['csv_file']) && $_FILES['csv_file']['error'] === UPLOAD_ERR_OK) {
            $tmpPath = $_FILES['csv_file']['tmp_name'];
            $studentController = new StudentController();
            $res = $studentController->importMatriculaCSV($tmpPath);
            $param = $res['success'] ? 'msg=' . urlencode($res['message']) : 'err=' . urlencode($res['message']);
        } else {
            $param = 'err=' . urlencode('Por favor seleccione un archivo CSV válido.');
        }
        header("Location: index.php?action=estudiantes&$param");
        exit;

    // --- ACCIONES DE INCIDENCIAS ---
    case 'crear_incidencia':
        require_once __DIR__ . '/views/crear_incidencia.php';
        break;

    case 'guardar_incidencia':
        $studentId = $_POST['student_id'] ?? null;
        $customStudentName = $_POST['custom_student_name'] ?? '';
        $courseId = $_POST['filter_course_id'] ?? null;
        $reporterUserId = $_POST['reporter_user_id'] ?? $_SESSION['user_id'];
        $customReporterName = $_POST['custom_reporter_name'] ?? '';
        $incidentTypeIds = $_POST['incident_type_ids'] ?? [];
        $observations = $_POST['observations'] ?? '';
        $customSeverity = $_POST['severity_level'] ?? null;

        $incidentController = new IncidentController();
        $res = $incidentController->createReport($studentId, $reporterUserId, $incidentTypeIds, $observations, $customSeverity, $customStudentName, $customReporterName, $courseId);

        if ($res['success']) {
            header("Location: index.php?action=reporte_estudiante&id=" . $res['report_id']);
            exit;
        } else {
            $err = urlencode($res['message']);
            header("Location: index.php?action=crear_incidencia&err=$err");
            exit;
        }

    case 'catalogo_faltas':
        AuthController::requireOrientadora();
        require_once __DIR__ . '/views/catalogo_faltas.php';
        break;

    // AGREGAR NUEVA INCIDENCIA AL CATÁLOGO (Botón + Agregar Nueva Incidencia)
    case 'guardar_incidencia_tipo':
        AuthController::requireOrientadora();
        $incidentController = new IncidentController();
        $res = $incidentController->addIncidentType($_POST);
        $param = $res['success'] ? 'msg=' . urlencode($res['message']) : 'err=' . urlencode($res['message']);
        header("Location: index.php?action=catalogo_faltas&$param");
        exit;

    case 'eliminar_incidencia_tipo':
        AuthController::requireOrientadora();
        $id = $_GET['id'] ?? null;
        $incidentController = new IncidentController();
        $res = $incidentController->deleteIncidentType($id);
        $param = $res['success'] ? 'msg=' . urlencode($res['message']) : 'err=' . urlencode($res['message']);
        header("Location: index.php?action=catalogo_faltas&$param");
        exit;

    // --- SEGUIMIENTO DE CASOS ---
    case 'seguimiento':
        AuthController::requireOrientadora();
        require_once __DIR__ . '/views/seguimiento.php';
        break;

    case 'guardar_seguimiento':
        AuthController::requireOrientadora();
        $reportId = $_POST['report_id'] ?? null;
        $actionTaken = $_POST['action_taken'] ?? '';
        $guardianPresent = isset($_POST['guardian_present']) ? 1 : 0;
        $agreementNotes = $_POST['agreement_notes'] ?? '';
        $nextDate = $_POST['next_date'] ?? null;
        $newStatus = $_POST['new_status'] ?? null;
        $userId = $_SESSION['user_id'];

        $followupController = new FollowupController();
        $res = $followupController->addFollowup($reportId, $userId, $actionTaken, $guardianPresent, $agreementNotes, $nextDate, $newStatus);
        $param = $res['success'] ? 'msg=' . urlencode($res['message']) : 'err=' . urlencode($res['message']);
        header("Location: index.php?action=seguimiento&$param");
        exit;

    // --- REPORTES E IMPRESIÓN ---
    case 'reporte_estudiante':
        require_once __DIR__ . '/views/reporte_estudiante.php';
        break;

    case 'reportes':
        AuthController::requireOrientadora();
        require_once __DIR__ . '/views/reporte_seccion.php';
        break;

    case 'mis_reportes':
        require_once __DIR__ . '/views/dashboard_docente.php';
        break;

    // --- GESTIÓN DE USUARIOS / MAESTROS ---
    case 'usuarios':
        AuthController::requireOrientadora();
        require_once __DIR__ . '/views/usuarios.php';
        break;

    case 'guardar_usuario':
        AuthController::requireOrientadora();
        $userController = new UserController();
        $res = $userController->createUser(
            $_POST['username'] ?? '',
            $_POST['password'] ?? '',
            $_POST['full_name'] ?? '',
            $_POST['role'] ?? 'docente',
            $_POST['email'] ?? '',
            $_POST['phone'] ?? ''
        );
        $param = $res['success'] ? 'msg=' . urlencode($res['message']) : 'err=' . urlencode($res['message']);
        header("Location: index.php?action=usuarios&$param");
        exit;

    case 'eliminar_usuario':
        AuthController::requireOrientadora();
        $id = $_GET['id'] ?? null;
        $userController = new UserController();
        $res = $userController->deleteUser($id, $_SESSION['user_id']);
        $param = $res['success'] ? 'msg=' . urlencode($res['message']) : 'err=' . urlencode($res['message']);
        header("Location: index.php?action=usuarios&$param");
        exit;

    default:
        header("Location: index.php?action=dashboard");
        exit;
}
