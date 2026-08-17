<?php
// controllers/FollowupController.php

require_once __DIR__ . '/../config/database.php';

class FollowupController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function addFollowup($reportId, $userId, $actionTaken, $guardianPresent = 0, $agreementNotes = '', $nextDate = null, $newStatus = null) {
        if (empty($reportId) || empty($actionTaken)) {
            return ['success' => false, 'message' => 'El reporte y la acción realizada son obligatorios.'];
        }

        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("
                INSERT INTO case_followups (report_id, user_id, action_taken, guardian_present, agreement_notes, next_date, status_assigned)
                VALUES (?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([
                $reportId,
                $userId,
                $actionTaken,
                $guardianPresent ? 1 : 0,
                $agreementNotes,
                !empty($nextDate) ? $nextDate : null,
                $newStatus
            ]);

            if (!empty($newStatus)) {
                $stmtUpd = $this->db->prepare("UPDATE incident_reports SET status = ? WHERE id = ?");
                $stmtUpd->execute([$newStatus, $reportId]);
            }

            $this->db->commit();
            return ['success' => true, 'message' => 'Seguimiento registrado y estado actualizado.'];
        } catch (PDOException $e) {
            $this->db->rollBack();
            return ['success' => false, 'message' => 'Error al guardar seguimiento: ' . $e->getMessage()];
        }
    }

    public function updateReportStatus($reportId, $status) {
        $validStatuses = ['Pendiente', 'Citatorio Enviado', 'Atendido en Orientación', 'Compromiso Firmado', 'Sanción Aplicada', 'Resuelto'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Estado inválido.'];
        }

        $stmt = $this->db->prepare("UPDATE incident_reports SET status = ? WHERE id = ?");
        try {
            $stmt->execute([$status, $reportId]);
            return ['success' => true, 'message' => 'Estado del reporte actualizado a: ' . $status];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar estado: ' . $e->getMessage()];
        }
    }

    public function getActiveCases() {
        $stmt = $this->db->query("
            SELECT r.*, s.full_name as student_name, s.identity_num, c.grade, c.modality, c.section_name,
                   u.full_name as reporter_name,
                   (SELECT COUNT(*) FROM case_followups f WHERE f.report_id = r.id) as followup_count,
                   (SELECT MAX(created_at) FROM case_followups f WHERE f.report_id = r.id) as last_followup_date
            FROM incident_reports r
            JOIN students s ON r.student_id = s.id
            LEFT JOIN courses c ON s.course_id = c.id
            JOIN users u ON r.reporter_user_id = u.id
            WHERE r.status != 'Resuelto'
            ORDER BY r.created_at DESC
        ");
        return $stmt->fetchAll();
    }
}
