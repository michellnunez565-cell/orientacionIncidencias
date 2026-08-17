<?php
// controllers/IncidentController.php

require_once __DIR__ . '/../config/database.php';

class IncidentController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // --- CATÁLOGO DE INCIDENCIAS / FALTAS ---

    public function getAllIncidentTypes($activeOnly = true) {
        $sql = "SELECT * FROM incident_types";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY severity DESC, category ASC, name ASC";
        $stmt = $this->db->query($sql);
        return $stmt->fetchAll();
    }

    public function getIncidentTypeById($id) {
        $stmt = $this->db->prepare("SELECT * FROM incident_types WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function addIncidentType($data) {
        if (empty($data['name'])) {
            return ['success' => false, 'message' => 'El nombre de la incidencia es obligatorio.'];
        }

        $code = !empty($data['code']) ? $data['code'] : 'INC-' . rand(100, 999);
        $category = !empty($data['category']) ? $data['category'] : 'General';
        $severity = !empty($data['severity']) && in_array($data['severity'], ['Leve', 'Grave', 'Muy Grave']) ? $data['severity'] : 'Leve';
        $description = $data['description'] ?? '';

        $stmt = $this->db->prepare("INSERT INTO incident_types (code, name, category, severity, description) VALUES (?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$code, $data['name'], $category, $severity, $description]);
            return ['success' => true, 'id' => $this->db->lastInsertId(), 'message' => 'Nueva incidencia agregada correctamente al catálogo.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al agregar incidencia: ' . $e->getMessage()];
        }
    }

    public function updateIncidentType($id, $data) {
        if (empty($data['name'])) {
            return ['success' => false, 'message' => 'El nombre de la incidencia es obligatorio.'];
        }

        $category = !empty($data['category']) ? $data['category'] : 'General';
        $severity = !empty($data['severity']) && in_array($data['severity'], ['Leve', 'Grave', 'Muy Grave']) ? $data['severity'] : 'Leve';
        $description = $data['description'] ?? '';
        $isActive = isset($data['is_active']) ? (int)$data['is_active'] : 1;

        $stmt = $this->db->prepare("UPDATE incident_types SET name = ?, category = ?, severity = ?, description = ?, is_active = ? WHERE id = ?");
        try {
            $stmt->execute([$data['name'], $category, $severity, $description, $isActive, $id]);
            return ['success' => true, 'message' => 'Incidencia actualizada en el catálogo.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }

    public function deleteIncidentType($id) {
        $stmt = $this->db->prepare("UPDATE incident_types SET is_active = 0 WHERE id = ?");
        try {
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Incidencia desactivada del catálogo.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }

    // --- REGISTRO DE REPORTES DE INCIDENCIA ---

    public function createReport($studentId, $reporterUserId, array $incidentTypeIds, $observations = '', $customSeverity = null, $customStudentName = '', $customReporterName = '', $courseId = null) {
        // 1. Si no viene student_id pero viene nombre escrito manualmente
        if (empty($studentId) && !empty($customStudentName)) {
            $customStudentName = trim($customStudentName);
            // Buscar si existe un alumno con este nombre
            $stmtCheck = $this->db->prepare("SELECT id FROM students WHERE full_name = ?");
            $stmtCheck->execute([$customStudentName]);
            $existing = $stmtCheck->fetch();

            if ($existing) {
                $studentId = $existing['id'];
            } else {
                // Registrar automáticamente nuevo alumno con el nombre escrito
                $code = 'EST-' . rand(10000, 99999);
                $stmtInsSt = $this->db->prepare("INSERT INTO students (student_code, full_name, course_id) VALUES (?, ?, ?)");
                $stmtInsSt->execute([$code, $customStudentName, $courseId]);
                $studentId = $this->db->lastInsertId();
            }
        }

        if (empty($studentId)) {
            return ['success' => false, 'message' => 'Debe seleccionar o escribir el nombre del estudiante.'];
        }
        if (empty($incidentTypeIds)) {
            return ['success' => false, 'message' => 'Debe seleccionar al menos una incidencia o falta.'];
        }

        // Determinar gravedad máxima si no fue provista
        if (empty($customSeverity)) {
            $placeholders = implode(',', array_fill(0, count($incidentTypeIds), '?'));
            $stmtSev = $this->db->prepare("SELECT severity FROM incident_types WHERE id IN ($placeholders)");
            $stmtSev->execute($incidentTypeIds);
            $severities = $stmtSev->fetchAll(PDO::FETCH_COLUMN);

            if (in_array('Muy Grave', $severities)) {
                $customSeverity = 'Muy Grave';
            } elseif (in_array('Grave', $severities)) {
                $customSeverity = 'Grave';
            } else {
                $customSeverity = 'Leve';
            }
        }

        $reportCode = 'REP-' . date('Ymd') . '-' . rand(100, 999);

        $this->db->beginTransaction();
        try {
            $stmtRep = $this->db->prepare("
                INSERT INTO incident_reports (report_code, student_id, reporter_user_id, custom_reporter_name, observations, severity_level, status) 
                VALUES (?, ?, ?, ?, ?, ?, 'Pendiente')
            ");
            $stmtRep->execute([
                $reportCode,
                $studentId,
                $reporterUserId,
                !empty($customReporterName) ? trim($customReporterName) : null,
                $observations,
                $customSeverity
            ]);
            $reportId = $this->db->lastInsertId();

            $stmtItem = $this->db->prepare("INSERT INTO incident_report_items (report_id, incident_type_id) VALUES (?, ?)");
            foreach ($incidentTypeIds as $incTypeId) {
                $stmtItem->execute([$reportId, $incTypeId]);
            }

            $this->db->commit();
            return ['success' => true, 'report_id' => $reportId, 'message' => 'Reporte disciplinario registrado correctamente.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al guardar el reporte: ' . $e->getMessage()];
        }
    }

    public function getAllReports($search = '', $status = null, $severity = null, $courseId = null, $reporterUserId = null) {
        $sql = "
            SELECT r.*, s.full_name as student_name, s.identity_num, s.student_code,
                   c.grade, c.modality, c.section_name,
                   COALESCE(r.custom_reporter_name, u.full_name) as reporter_name
            FROM incident_reports r
            JOIN students s ON r.student_id = s.id
            LEFT JOIN courses c ON s.course_id = c.id
            JOIN users u ON r.reporter_user_id = u.id
            WHERE 1=1
        ";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (s.full_name LIKE ? OR s.identity_num LIKE ? OR r.report_code LIKE ? OR r.custom_reporter_name LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($status)) {
            $sql .= " AND r.status = ?";
            $params[] = $status;
        }

        if (!empty($severity)) {
            $sql .= " AND r.severity_level = ?";
            $params[] = $severity;
        }

        if (!empty($courseId)) {
            $sql .= " AND s.course_id = ?";
            $params[] = $courseId;
        }

        if (!empty($reporterUserId)) {
            $sql .= " AND r.reporter_user_id = ?";
            $params[] = $reporterUserId;
        }

        $sql .= " ORDER BY r.created_at DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getReportById($id) {
        $stmtRep = $this->db->prepare("
            SELECT r.*, s.full_name as student_name, s.identity_num, s.student_code, s.guardian_name, s.guardian_phone, s.guardian_address,
                   c.grade, c.modality, c.section_name,
                   COALESCE(r.custom_reporter_name, u.full_name) as reporter_name
            FROM incident_reports r
            JOIN students s ON r.student_id = s.id
            LEFT JOIN courses c ON s.course_id = c.id
            JOIN users u ON r.reporter_user_id = u.id
            WHERE r.id = ?
        ");
        $stmtRep->execute([$id]);
        $report = $stmtRep->fetch();

        if (!$report) return null;

        $stmtItems = $this->db->prepare("
            SELECT t.* 
            FROM incident_report_items ri
            JOIN incident_types t ON ri.incident_type_id = t.id
            WHERE ri.report_id = ?
        ");
        $stmtItems->execute([$id]);
        $report['items'] = $stmtItems->fetchAll();

        $stmtFollow = $this->db->prepare("
            SELECT f.*, u.full_name as user_name 
            FROM case_followups f
            JOIN users u ON f.user_id = u.id
            WHERE f.report_id = ?
            ORDER BY f.created_at ASC
        ");
        $stmtFollow->execute([$id]);
        $report['followups'] = $stmtFollow->fetchAll();

        return $report;
    }
}
