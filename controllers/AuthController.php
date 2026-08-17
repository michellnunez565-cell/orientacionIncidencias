<?php
// controllers/AuthController.php

require_once __DIR__ . '/../config/database.php';

class AuthController {
    private $db;

    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->db = Database::getInstance()->getConnection();
    }

    // Verificar si hay algún usuario en la base de datos
    public function hasUsers() {
        $stmt = $this->db->query("SELECT COUNT(*) FROM users");
        return $stmt->fetchColumn() > 0;
    }

    // Registrar Orientadora inicial (Setup)
    public function setupInitialAdmin($username, $password, $fullName, $email, $phone) {
        if ($this->hasUsers()) {
            return ['success' => false, 'message' => 'El sistema ya ha sido configurado previamente.'];
        }

        if (empty($username) || empty($password) || empty($fullName)) {
            return ['success' => false, 'message' => 'Todos los campos obligatorios deben completarse.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password_hash, full_name, role, email, phone) VALUES (?, ?, ?, 'orientadora', ?, ?)");
        
        try {
            $stmt->execute([$username, $hash, $fullName, $email, $phone]);
            // Iniciar sesión automáticamente
            $userId = $this->db->lastInsertId();
            $_SESSION['user_id'] = $userId;
            $_SESSION['username'] = $username;
            $_SESSION['full_name'] = $fullName;
            $_SESSION['role'] = 'orientadora';

            return ['success' => true, 'message' => 'Cuenta de Orientadora configurada con éxito.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al crear la cuenta: ' . $e->getMessage()];
        }
    }

    // Inicio de Sesión
    public function login($username, $password) {
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Por favor ingrese usuario y contraseña.'];
        }

        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];

            return ['success' => true, 'role' => $user['role']];
        }

        return ['success' => false, 'message' => 'Usuario o contraseña incorrectos.'];
    }

    // Cerrar Sesión
    public function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000,
                $params["path"], $params["domain"],
                $params["secure"], $params["httponly"]
            );
        }
        session_destroy();
    }

    // Verificaciones de estado
    public static function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        return isset($_SESSION['user_id']);
    }

    public static function isOrientadora() {
        return self::isLoggedIn() && $_SESSION['role'] === 'orientadora';
    }

    public static function isDocente() {
        return self::isLoggedIn() && $_SESSION['role'] === 'docente';
    }

    public static function requireLogin() {
        if (!self::isLoggedIn()) {
            header("Location: index.php?action=login");
            exit;
        }
    }

    public static function requireOrientadora() {
        self::requireLogin();
        if (!self::isOrientadora()) {
            header("Location: index.php?action=dashboard");
            exit;
        }
    }
}
