<?php
// ============================================================
// InvControl – Modelo: Proveedor
// ============================================================
class Proveedor {
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM proveedores ORDER BY nombre")->fetchAll();
    }
    public function getById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM proveedores WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    public function create(array $data): bool {
        $stmt = $this->db->prepare("INSERT INTO proveedores (nombre, contacto, telefono, email) VALUES (:nombre, :contacto, :telefono, :email)");
        return $stmt->execute($data);
    }
    public function update(int $id, array $data): bool {
        $data['id'] = $id;
        $stmt = $this->db->prepare("UPDATE proveedores SET nombre=:nombre, contacto=:contacto, telefono=:telefono, email=:email WHERE id=:id");
        return $stmt->execute($data);
    }
    public function delete(int $id): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM productos WHERE proveedor_id = ? AND activo = 1");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) return false;
        $stmt = $this->db->prepare("DELETE FROM proveedores WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
