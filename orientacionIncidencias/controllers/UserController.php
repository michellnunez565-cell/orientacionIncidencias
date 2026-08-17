<?php
// controllers/UserController.php

require_once __DIR__ . '/../config/database.php';

class UserController {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAllUsers() {
        $stmt = $this->db->query("SELECT id, username, full_name, role, email, phone, created_at FROM users ORDER BY role ASC, full_name ASC");
        return $stmt->fetchAll();
    }

    public function createUser($username, $password, $fullName, $role = 'docente', $email = '', $phone = '') {
        if (empty($username) || empty($password) || empty($fullName)) {
            return ['success' => false, 'message' => 'Usuario, contraseña y nombre completo son obligatorios.'];
        }

        if (!in_array($role, ['orientadora', 'docente'])) {
            $role = 'docente';
        }

        $stmtCheck = $this->db->prepare("SELECT id FROM users WHERE username = ?");
        $stmtCheck->execute([$username]);
        if ($stmtCheck->fetch()) {
            return ['success' => false, 'message' => 'El nombre de usuario ya existe. Elija otro.'];
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO users (username, password_hash, full_name, role, email, phone) VALUES (?, ?, ?, ?, ?, ?)");
        try {
            $stmt->execute([$username, $hash, $fullName, $role, $email, $phone]);
            return ['success' => true, 'id' => $this->db->lastInsertId(), 'message' => 'Usuario creado exitosamente.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al crear usuario: ' . $e->getMessage()];
        }
    }

    public function deleteUser($id, $currentUserId) {
        if ($id == $currentUserId) {
            return ['success' => false, 'message' => 'No puede eliminar su propio usuario mientras tiene la sesión activa.'];
        }

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        try {
            $stmt->execute([$id]);
            return ['success' => true, 'message' => 'Usuario eliminado.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error al eliminar usuario: ' . $e->getMessage()];
        }
    }
}
