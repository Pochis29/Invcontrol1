<?php
// ============================================================
// InvControl – Modelo: Categoria (con prefijo)
// ============================================================
class Categoria {
    private PDO $db;
    public function __construct() { $this->db = Database::getConnection(); }

    public function getAll(): array {
        return $this->db->query("SELECT * FROM categorias ORDER BY nombre")->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare("SELECT * FROM categorias WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function create(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO categorias (nombre, prefijo, descripcion)
            VALUES (:nombre, :prefijo, :descripcion)
        ");
        return $stmt->execute($data);
    }

    public function update(int $id, array $data): bool {
        $data['id'] = $id;
        $stmt = $this->db->prepare("
            UPDATE categorias
            SET nombre = :nombre, prefijo = :prefijo, descripcion = :descripcion
            WHERE id = :id
        ");
        return $stmt->execute($data);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare(
            "SELECT COUNT(*) FROM productos WHERE categoria_id = ? AND activo = 1"
        );
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) return false;
        $stmt = $this->db->prepare("DELETE FROM categorias WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // ── Genera el siguiente código secuencial para una categoría ──
    // Ej: si prefijo = SNK y ya hay SNK-003 → devuelve SNK-004
    public function siguienteCodigo(int $categoriaId): string {
        $cat = $this->getById($categoriaId);
        if (!$cat || empty($cat['prefijo'])) {
            // Sin prefijo: usar PROD-XXX genérico
            $stmt = $this->db->query(
                "SELECT codigo FROM productos WHERE activo = 1 AND codigo LIKE 'PROD-%' ORDER BY codigo DESC LIMIT 1"
            );
            $ultimo = $stmt->fetchColumn();
            $num    = $ultimo ? ((int)substr($ultimo, 5)) + 1 : 1;
            return 'PROD-' . str_pad($num, 3, '0', STR_PAD_LEFT);
        }

        $prefijo = strtoupper(trim($cat['prefijo']));
        $stmt    = $this->db->prepare(
            "SELECT codigo FROM productos
             WHERE activo = 1 AND codigo LIKE ?
             ORDER BY codigo DESC LIMIT 1"
        );
        $stmt->execute([$prefijo . '-%']);
        $ultimo = $stmt->fetchColumn();

        if ($ultimo) {
            // Extraer número del último código (Ej: SNK-007 → 7)
            $partes = explode('-', $ultimo);
            $num    = ((int)end($partes)) + 1;
        } else {
            $num = 1;
        }

        return $prefijo . '-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }
}
