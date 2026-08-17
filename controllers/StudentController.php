<?php
// controllers/StudentController.php

require_once __DIR__ . '/../config/database.php';

class StudentController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllStudents($search = '', $courseId = null) {
        $sql = "SELECT s.*, c.grade, c.modality, c.section_name,
                (SELECT COUNT(*) FROM incident_reports r WHERE r.student_id = s.id) as total_incidents
                FROM students s
                LEFT JOIN courses c ON s.course_id = c.id
                WHERE 1=1";
        $params = [];

        if (!empty($search)) {
            $sql .= " AND (s.full_name LIKE ? OR s.identity_num LIKE ? OR s.student_code LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }

        if (!empty($courseId)) {
            $sql .= " AND s.course_id = ?";
            $params[] = $courseId;
        }

        $sql .= " ORDER BY c.grade ASC, c.modality ASC, c.section_name ASC, s.full_name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getStudentById($id) {
        $stmt = $this->db->prepare("
            SELECT s.*, c.grade, c.modality, c.section_name 
            FROM students s 
            LEFT JOIN courses c ON s.course_id = c.id 
            WHERE s.id = ?
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAllCourses() {
        $stmt = $this->db->query("SELECT * FROM courses ORDER BY grade ASC, modality ASC, section_name ASC");
        return $stmt->fetchAll();
    }

    public function addStudent($data) {
        if (empty($data['full_name'])) {
            return ['success' => false, 'message' => 'El nombre completo es obligatorio.'];
        }

        $code = !empty($data['student_code']) ? $data['student_code'] : 'EST-' . rand(10000, 99999);
        
        $sql = "INSERT INTO students (student_code, identity_num, full_name, gender, birth_date, course_id, guardian_name, guardian_phone, guardian_address, documents_submitted)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        
        try {
            $stmt->execute([
                $code,
                $data['identity_num'] ?? null,
                $data['full_name'],
                $data['gender'] ?? 'M',
                $data['birth_date'] ?? null,
                $data['course_id'] ?? null,
                $data['guardian_name'] ?? null,
                $data['guardian_phone'] ?? null,
                $data['guardian_address'] ?? null,
                $data['documents_submitted'] ?? null
            ]);
            return ['success' => true, 'id' => $this->db->lastInsertId(), 'message' => 'Estudiante registrado correctamente.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al registrar estudiante: ' . $e->getMessage()];
        }
    }

    public function updateStudent($id, $data) {
        $sql = "UPDATE students SET 
                identity_num = ?, full_name = ?, gender = ?, birth_date = ?, 
                course_id = ?, guardian_name = ?, guardian_phone = ?, guardian_address = ?, documents_submitted = ?
                WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        try {
            $stmt->execute([
                $data['identity_num'] ?? null,
                $data['full_name'],
                $data['gender'] ?? 'M',
                $data['birth_date'] ?? null,
                $data['course_id'] ?? null,
                $data['guardian_name'] ?? null,
                $data['guardian_phone'] ?? null,
                $data['guardian_address'] ?? null,
                $data['documents_submitted'] ?? null,
                $id
            ]);
            return ['success' => true, 'message' => 'Información del estudiante actualizada.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()];
        }
    }

    public function deleteStudent($id) {
        $stmt = $this->db->prepare("DELETE FROM students WHERE id = ?");
        try {
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Estudiante eliminado.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar: ' . $e->getMessage()];
        }
    }

    // Importar o Sincronizar desde archivo de Matrícula (CSV)
    public function importMatriculaCSV($filePath) {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return ['success' => false, 'message' => 'No se puede leer el archivo de matrícula.'];
        }

        $handle = fopen($filePath, 'r');
        if (!$handle) {
            return ['success' => false, 'message' => 'Error al abrir el archivo.'];
        }

        $imported = 0;
        $updated = 0;
        $errors = 0;
        $row = 0;

        // Mapeo de cursos existentes
        $courses = $this->getAllCourses();
        $courseMap = [];
        foreach ($courses as $c) {
            $key = strtolower(trim($c['grade']) . '|' . trim($c['modality']) . '|' . trim($c['section_name']));
            $courseMap[$key] = $c['id'];
        }

        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $row++;
            // Omitir encabezado si tiene texto
            if ($row === 1 && (strpos(strtolower($data[0]), 'nombre') !== false || strpos(strtolower($data[0]), 'identidad') !== false || strpos(strtolower($data[1]), 'nombre') !== false)) {
                continue;
            }

            // Formato esperado CSV:
            // Col 0: Identidad/RNV
            // Col 1: Nombre Completo
            // Col 2: Grado (e.g. 7mo, 8vo, 9no, 10mo, 11mo, 12mo)
            // Col 3: Modalidad (e.g. Ciclo Básico, BTP en Informática, BTP en Electromecánica, BTP en Desarrollo Agroforestal)
            // Col 4: Sección (e.g. Sección 1, Sección 2, Sección 3)
            // Col 5: Nombre Padre/Tutor
            // Col 6: Teléfono

            if (count($data) < 2) continue;

            $identity = trim($data[0]);
            $fullName = trim($data[1]);
            $grade = isset($data[2]) ? trim($data[2]) : '7mo';
            $modality = isset($data[3]) ? trim($data[3]) : 'Ciclo Básico';
            $section = isset($data[4]) ? trim($data[4]) : 'Sección 1';
            $guardianName = isset($data[5]) ? trim($data[5]) : '';
            $guardianPhone = isset($data[6]) ? trim($data[6]) : '';

            if (empty($fullName)) continue;

            // Buscar o crear curso si no existe exactamente
            $cKey = strtolower($grade . '|' . $modality . '|' . $section);
            $courseId = null;

            if (isset($courseMap[$cKey])) {
                $courseId = $courseMap[$cKey];
            } else {
                // Crear curso dinámicamente si es un nuevo curso en Matrícula
                $stmtNewC = $this->db->prepare("INSERT INTO courses (grade, modality, section_name) VALUES (?, ?, ?)");
                try {
                    $stmtNewC->execute([$grade, $modality, $section]);
                    $courseId = $this->db->lastInsertId();
                    $courseMap[$cKey] = $courseId;
                } catch (PDOException $e) {
                    // Si ya existe
                    $stmtFind = $this->db->prepare("SELECT id FROM courses WHERE grade=? AND modality=? AND section_name=?");
                    $stmtFind->execute([$grade, $modality, $section]);
                    $cRes = $stmtFind->fetch();
                    $courseId = $cRes['id'] ?? null;
                }
            }

            // Comprobar si el alumno ya existe por Identidad o por Nombre
            $stmtCheck = $this->db->prepare("SELECT id FROM students WHERE (identity_num = ? AND identity_num != '') OR full_name = ?");
            $stmtCheck->execute([$identity, $fullName]);
            $existingStudent = $stmtCheck->fetch();

            if ($existingStudent) {
                // Actualizar datos de matrícula
                $stmtUpd = $this->db->prepare("UPDATE students SET identity_num = ?, course_id = ?, guardian_name = ?, guardian_phone = ? WHERE id = ?");
                $stmtUpd->execute([$identity, $courseId, $guardianName, $guardianPhone, $existingStudent['id']]);
                $updated++;
            } else {
                // Insertar nuevo
                $code = 'EST-' . rand(10000, 99999);
                $stmtIns = $this->db->prepare("INSERT INTO students (student_code, identity_num, full_name, course_id, guardian_name, guardian_phone) VALUES (?, ?, ?, ?, ?, ?)");
                try {
                    $stmtIns->execute([$code, $identity, $fullName, $courseId, $guardianName, $guardianPhone]);
                    $imported++;
                } catch (PDOException $e) {
                    $errors++;
                }
            }
        }
        fclose($handle);

        return [
            'success' => true,
            'message' => "Proceso de actualización completado. Estudiantes nuevos registrados: $imported. Estudiantes actualizados: $updated."
        ];
    }
}
