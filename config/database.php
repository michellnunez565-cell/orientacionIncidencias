<?php
// config/database.php

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dbFile = __DIR__ . '/../database.sqlite';
        try {
            $this->pdo = new PDO("sqlite:" . $dbFile);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->pdo->exec("PRAGMA foreign_keys = ON;");
            $this->initTables();
        } catch (PDOException $e) {
            die("Error de conexión a la base de datos: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    private function initTables() {
        // Tablas principales
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                username TEXT UNIQUE NOT NULL,
                password_hash TEXT NOT NULL,
                full_name TEXT NOT NULL,
                role TEXT CHECK(role IN ('orientadora', 'docente')) NOT NULL,
                email TEXT,
                phone TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS courses (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                grade TEXT NOT NULL,
                modality TEXT NOT NULL,
                section_name TEXT NOT NULL,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
                UNIQUE(grade, modality, section_name)
            );

            CREATE TABLE IF NOT EXISTS students (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                student_code TEXT UNIQUE,
                identity_num TEXT UNIQUE,
                full_name TEXT NOT NULL,
                gender TEXT CHECK(gender IN ('M', 'F')),
                birth_date DATE,
                course_id INTEGER REFERENCES courses(id) ON DELETE SET NULL,
                guardian_name TEXT,
                guardian_phone TEXT,
                guardian_address TEXT,
                documents_submitted TEXT,
                status TEXT DEFAULT 'activo',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS incident_types (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                code TEXT UNIQUE,
                name TEXT NOT NULL,
                category TEXT DEFAULT 'General',
                severity TEXT CHECK(severity IN ('Leve', 'Grave', 'Muy Grave')) DEFAULT 'Leve',
                description TEXT,
                is_active INTEGER DEFAULT 1,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS incident_reports (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                report_code TEXT UNIQUE NOT NULL,
                student_id INTEGER NOT NULL REFERENCES students(id) ON DELETE CASCADE,
                reporter_user_id INTEGER NOT NULL REFERENCES users(id),
                custom_reporter_name TEXT,
                report_date DATE DEFAULT (DATE('now', 'localtime')),
                report_time TIME DEFAULT (TIME('now', 'localtime')),
                observations TEXT,
                severity_level TEXT CHECK(severity_level IN ('Leve', 'Grave', 'Muy Grave')) DEFAULT 'Leve',
                status TEXT CHECK(status IN ('Pendiente', 'Citatorio Enviado', 'Atendido en Orientación', 'Compromiso Firmado', 'Sanción Aplicada', 'Resuelto')) DEFAULT 'Pendiente',
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );

            CREATE TABLE IF NOT EXISTS incident_report_items (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                report_id INTEGER NOT NULL REFERENCES incident_reports(id) ON DELETE CASCADE,
                incident_type_id INTEGER NOT NULL REFERENCES incident_types(id) ON DELETE CASCADE
            );

            CREATE TABLE IF NOT EXISTS case_followups (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                report_id INTEGER NOT NULL REFERENCES incident_reports(id) ON DELETE CASCADE,
                followup_date DATE DEFAULT (DATE('now', 'localtime')),
                user_id INTEGER NOT NULL REFERENCES users(id),
                action_taken TEXT NOT NULL,
                guardian_present INTEGER DEFAULT 0,
                agreement_notes TEXT,
                next_date DATE,
                status_assigned TEXT,
                created_at DATETIME DEFAULT CURRENT_TIMESTAMP
            );
        ");

        // Añadir columna si no existe
        try {
            $this->pdo->exec("ALTER TABLE incident_reports ADD COLUMN custom_reporter_name TEXT;");
        } catch (PDOException $e) {
            // Ya existe
        }

        $this->seedInitialData();
    }

    private function seedInitialData() {
        // 1. Sembrar Cursos y Modalidades
        $stmtCount = $this->pdo->query("SELECT COUNT(*) FROM courses");
        if ($stmtCount->fetchColumn() == 0) {
            $courses = [
                ['grade' => '7mo', 'modality' => 'Ciclo Básico', 'section_name' => 'Sección 1'],
                ['grade' => '7mo', 'modality' => 'Ciclo Básico', 'section_name' => 'Sección 2'],
                ['grade' => '7mo', 'modality' => 'Ciclo Básico', 'section_name' => 'Sección 3'],
                ['grade' => '8vo', 'modality' => 'Ciclo Básico', 'section_name' => 'Sección 1'],
                ['grade' => '8vo', 'modality' => 'Ciclo Básico', 'section_name' => 'Sección 2'],
                ['grade' => '8vo', 'modality' => 'Ciclo Básico', 'section_name' => 'Sección 3'],
                ['grade' => '9no', 'modality' => 'Ciclo Básico', 'section_name' => 'Sección 1'],
                ['grade' => '9no', 'modality' => 'Ciclo Básico', 'section_name' => 'Sección 2'],
                ['grade' => '9no', 'modality' => 'Ciclo Básico', 'section_name' => 'Sección 3'],
                ['grade' => '10mo', 'modality' => 'BTP en Informática', 'section_name' => 'Sección 1'],
                ['grade' => '10mo', 'modality' => 'BTP en Informática', 'section_name' => 'Sección 2'],
                ['grade' => '10mo', 'modality' => 'BTP en Electromecánica', 'section_name' => 'Sección 1'],
                ['grade' => '10mo', 'modality' => 'BTP en Desarrollo Agroforestal', 'section_name' => 'Sección 1'],
                ['grade' => '11mo', 'modality' => 'BTP en Informática', 'section_name' => 'Sección 1'],
                ['grade' => '11mo', 'modality' => 'BTP en Informática', 'section_name' => 'Sección 2'],
                ['grade' => '11mo', 'modality' => 'BTP en Electromecánica', 'section_name' => 'Sección 1'],
                ['grade' => '11mo', 'modality' => 'BTP en Desarrollo Agroforestal', 'section_name' => 'Sección 1'],
                ['grade' => '12mo', 'modality' => 'BTP en Informática', 'section_name' => 'Sección 1'],
                ['grade' => '12mo', 'modality' => 'BTP en Electromecánica', 'section_name' => 'Sección 1'],
                ['grade' => '12mo', 'modality' => 'BTP en Desarrollo Agroforestal', 'section_name' => 'Sección 1'],
            ];

            $stmtIns = $this->pdo->prepare("INSERT INTO courses (grade, modality, section_name) VALUES (?, ?, ?)");
            foreach ($courses as $c) {
                $stmtIns->execute([$c['grade'], $c['modality'], $c['section_name']]);
            }
        }

        // 2. Sembrar Faltas / Incidencias base extraídas de la hoja física
        $stmtInc = $this->pdo->query("SELECT COUNT(*) FROM incident_types");
        if ($stmtInc->fetchColumn() == 0) {
            $defaultIncidents = [
                ['code' => 'INC-001', 'name' => 'No trabaja en clase', 'category' => 'Académica', 'severity' => 'Leve', 'description' => 'El estudiante se niega o descuida realizar el trabajo asignado en el aula.'],
                ['code' => 'INC-002', 'name' => 'Interrumpe en clases', 'category' => 'Conductual', 'severity' => 'Leve', 'description' => 'Realiza comentarios, sonidos o acciones que distraen el desarrollo normal de la clase.'],
                ['code' => 'INC-003', 'name' => 'Juega durante la clase', 'category' => 'Conductual', 'severity' => 'Leve', 'description' => 'Realiza actividades de juego descontextualizadas durante la explicación o trabajo escolar.'],
                ['code' => 'INC-004', 'name' => 'Uso inadecuado de vocabulario', 'category' => 'Respeto', 'severity' => 'Leve', 'description' => 'Utiliza palabras soeces, vulgarismos o lenguaje inapropiado.'],
                ['code' => 'INC-005', 'name' => 'Maltrato al material del aula', 'category' => 'Cuidado Institucional', 'severity' => 'Grave', 'description' => 'Daña o usa incorrectamente pupitres, pizarrón, herramientas o infraestructura del aula.'],
                ['code' => 'INC-006', 'name' => 'Uso inadecuado de su material', 'category' => 'Cuidado Institucional', 'severity' => 'Leve', 'description' => 'Desperdicia o destruye sus propios útiles o pertenencias.'],
                ['code' => 'INC-007', 'name' => 'No se mantiene en su lugar', 'category' => 'Conductual', 'severity' => 'Leve', 'description' => 'Se desplaza contantemente por el aula sin autorización del docente.'],
                ['code' => 'INC-008', 'name' => 'Agresión física a su compañero', 'category' => 'Convivencia y Seguridad', 'severity' => 'Muy Grave', 'description' => 'Empujones, golpes o violencia física hacia otros compañeros.'],
                ['code' => 'INC-009', 'name' => 'Agresión verbal', 'category' => 'Respeto y Convivencia', 'severity' => 'Grave', 'description' => 'Insultos, amenazas, ofensas dirigidas a compañeros o personal escolar.']
            ];

            $stmtInsInc = $this->pdo->prepare("INSERT INTO incident_types (code, name, category, severity, description) VALUES (?, ?, ?, ?, ?)");
            foreach ($defaultIncidents as $inc) {
                $stmtInsInc->execute([$inc['code'], $inc['name'], $inc['category'], $inc['severity'], $inc['description']]);
            }
        }
    }
}
