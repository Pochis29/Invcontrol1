<?php
// ============================================================
// InvControl – Modelo: Movimiento (Kardex)
// ============================================================

class Movimiento {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function registrar(array $data): bool {
        $stmt = $this->db->prepare("
            INSERT INTO movimientos (producto_id, usuario_id, tipo, cantidad, stock_resultante, observacion)
            VALUES (:producto_id, :usuario_id, :tipo, :cantidad, :stock_resultante, :observacion)
        ");
        return $stmt->execute($data);
    }

    public function getKardex(int $productoId): array {
        $stmt = $this->db->prepare("
            SELECT m.*, u.nombre AS usuario_nombre
            FROM movimientos m
            JOIN usuarios u ON m.usuario_id = u.id
            WHERE m.producto_id = ?
            ORDER BY m.fecha DESC
        ");
        $stmt->execute([$productoId]);
        return $stmt->fetchAll();
    }

    public function getByFecha(string $desde, string $hasta, ?int $productoId = null): array {
        $sql = "
            SELECT m.*, u.nombre AS usuario_nombre, p.nombre AS producto_nombre, p.codigo
            FROM movimientos m
            JOIN usuarios u  ON m.usuario_id  = u.id
            JOIN productos p ON m.producto_id = p.id
            WHERE m.fecha BETWEEN ? AND ?
        ";
        $params = [$desde . ' 00:00:00', $hasta . ' 23:59:59'];
        if ($productoId) {
            $sql .= " AND m.producto_id = ?";
            $params[] = $productoId;
        }
        $sql .= " ORDER BY m.fecha DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function getResumenHoy(): array {
        $stmt = $this->db->prepare("
            SELECT
                SUM(CASE WHEN tipo = 'entrada' THEN cantidad ELSE 0 END) AS entradas_hoy,
                SUM(CASE WHEN tipo = 'salida'  THEN cantidad ELSE 0 END) AS salidas_hoy
            FROM movimientos
            WHERE DATE(fecha) = CURDATE()
        ");
        $stmt->execute();
        return $stmt->fetch();
    }
}
