<?php
// ============================================================
// InvControl – Modelo: Usuario
// ============================================================

class Usuario {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    /**
     * Autenticar usuario por email y contraseña
     */
    public function login(string $email, string $password): array|false {
        $stmt = $this->db->prepare(
            "SELECT id, nombre, email, password_hash, rol FROM usuarios WHERE email = ? AND activo = 1"
        );
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();

        if ($usuario && password_verify($password, $usuario['password_hash'])) {
            return $usuario;
        }
        return false;
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getAll(): array {
        return $this->db->query("SELECT id, nombre, email, rol, activo, created_at FROM usuarios ORDER BY nombre")->fetchAll();
    }
}
