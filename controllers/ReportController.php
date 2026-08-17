<?php
// controllers/ReportController.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/IncidentController.php';

class ReportController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getStudentIndividualReport($reportId) {
        $incidentController = new IncidentController();
        $report = $incidentController->getReportById($reportId);
        if (!$report) return null;

        // Cargar todas las faltas activas del catálogo para construir el formulario/checksheet
        $allIncidentTypes = $incidentController->getAllIncidentTypes(false);

        // Mapear los IDs de las faltas que fueron marcadas en este reporte
        $checkedIds = array_map(function($item) {
            return $item['id'];
        }, $report['items']);

        return [
            'report' => $report,
            'all_incident_types' => $allIncidentTypes,
            'checked_ids' => $checkedIds
        ];
    }

    public function getSectionReport($courseId, $startDate = null, $endDate = null) {
        $stmtCourse = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmtCourse->execute([$courseId]);
        $course = $stmtCourse->fetch();

        if (!$course) return null;

        $sql = "
            SELECT r.*, s.full_name as student_name, s.identity_num, s.student_code,
                   u.full_name as reporter_name,
                   (SELECT COUNT(*) FROM incident_report_items ri WHERE ri.report_id = r.id) as item_count
            FROM incident_reports r
            JOIN students s ON r.student_id = s.id
            JOIN users u ON r.reporter_user_id = u.id
            WHERE s.course_id = ?
        ";
        $params = [$courseId];

        if (!empty($startDate)) {
            $sql .= " AND r.report_date >= ?";
            $params[] = $startDate;
        }

        if (!empty($endDate)) {
            $sql .= " AND r.report_date <= ?";
            $params[] = $endDate;
        }

        $sql .= " ORDER BY r.report_date DESC, s.full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $reports = $stmt->fetchAll();

        // Estadísticas por gravedad y por tipo de incidencia
        $stats = [
            'total' => count($reports),
            'leve' => 0,
            'grave' => 0,
            'muy_grave' => 0,
            'resueltos' => 0,
            'pendientes' => 0
        ];

        foreach ($reports as $r) {
            if ($r['severity_level'] === 'Leve') $stats['leve']++;
            elseif ($r['severity_level'] === 'Grave') $stats['grave']++;
            elseif ($r['severity_level'] === 'Muy Grave') $stats['muy_grave']++;

            if ($r['status'] === 'Resuelto') $stats['resueltos']++;
            else $stats['pendientes']++;
        }

        return [
            'course' => $course,
            'reports' => $reports,
            'stats' => $stats
        ];
    }
}
