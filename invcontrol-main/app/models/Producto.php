<?php
// ============================================================
// InvControl – Modelo: Producto (con gestión de empaques WMS)
// ============================================================

class Producto {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function getAll(): array {
        return $this->db->query("
            SELECT p.*, c.nombre AS categoria_nombre, pr.nombre AS proveedor_nombre,
                   (p.stock_actual <= p.stock_minimo) AS bajo_stock
            FROM productos p
            LEFT JOIN categorias c  ON p.categoria_id  = c.id
            LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
            WHERE p.activo = 1
            ORDER BY p.nombre
        ")->fetchAll();
    }

    public function getById(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT p.*, c.nombre AS categoria_nombre, pr.nombre AS proveedor_nombre
            FROM productos p
            LEFT JOIN categorias c  ON p.categoria_id  = c.id
            LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
            WHERE p.id = ? AND p.activo = 1
        ");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    /** Crea un producto y retorna el ID generado */
    public function create(array $data): int {
        $stmt = $this->db->prepare("
            INSERT INTO productos (codigo, codigo_barras, unidad_base, nombre, descripcion, categoria_id, proveedor_id, stock_actual, stock_minimo)
            VALUES (:codigo, :codigo_barras, :unidad_base, :nombre, :descripcion, :categoria_id, :proveedor_id, :stock_actual, :stock_minimo)
        ");
        $stmt->execute($data);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $data['id'] = $id;
        $stmt = $this->db->prepare("
            UPDATE productos SET
                codigo        = :codigo,
                codigo_barras = :codigo_barras,
                unidad_base   = :unidad_base,
                nombre        = :nombre,
                descripcion   = :descripcion,
                categoria_id  = :categoria_id,
                proveedor_id  = :proveedor_id,
                stock_minimo  = :stock_minimo
            WHERE id = :id AND activo = 1
        ");
        return $stmt->execute($data);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("UPDATE productos SET activo = 0 WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function codigoExiste(string $codigo, int $excludeId = 0): bool {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM productos WHERE codigo = ? AND id != ? AND activo = 1");
        $stmt->execute([$codigo, $excludeId]);
        return $stmt->fetchColumn() > 0;
    }

    public function updateStock(int $id, int $nuevoStock): bool {
        $stmt = $this->db->prepare("UPDATE productos SET stock_actual = ? WHERE id = ?");
        return $stmt->execute([$nuevoStock, $id]);
    }

    public function getProductosBajoStock(): array {
        return $this->db->query("
            SELECT p.*, c.nombre AS categoria_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.activo = 1 AND p.stock_actual <= p.stock_minimo
            ORDER BY (p.stock_minimo - p.stock_actual) DESC
        ")->fetchAll();
    }

    /** Busca por código de barras: primero en producto, luego en empaques */
    public function getByCodigoBarras(string $codigo_barras): array|false {
        // 1. Buscar en el producto directamente
        $stmt = $this->db->prepare("
            SELECT p.*, c.nombre AS categoria_nombre, pr.nombre AS proveedor_nombre,
                   NULL AS empaque_id, NULL AS empaque_nombre, NULL AS empaque_cantidad
            FROM productos p
            LEFT JOIN categorias c  ON p.categoria_id = c.id
            LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
            WHERE p.codigo_barras = ? AND p.activo = 1
        ");
        $stmt->execute([$codigo_barras]);
        $producto = $stmt->fetch();
        if ($producto) return $producto;

        // 2. Buscar en los empaques (caja, paquete, etc.)
        $stmt2 = $this->db->prepare("
            SELECT p.*, c.nombre AS categoria_nombre, pr.nombre AS proveedor_nombre,
                   ue.id AS empaque_id, ue.nombre AS empaque_nombre, ue.cantidad AS empaque_cantidad
            FROM unidades_empaque ue
            JOIN productos p ON ue.producto_id = p.id
            LEFT JOIN categorias c  ON p.categoria_id = c.id
            LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
            WHERE ue.codigo_barras = ? AND ue.activo = 1 AND p.activo = 1
        ");
        $stmt2->execute([$codigo_barras]);
        return $stmt2->fetch();
    }

    public function buscarPorNombre(string $termino): array {
        $stmt = $this->db->prepare("
            SELECT p.*, c.nombre AS categoria_nombre
            FROM productos p
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE p.activo = 1 AND p.nombre LIKE ?
            ORDER BY p.nombre
            LIMIT 10
        ");
        $stmt->execute(['%' . $termino . '%']);
        return $stmt->fetchAll();
    }

    // ══════════════════════════════════════════════════════════
    // GESTIÓN DE EMPAQUES (WMS)
    // ══════════════════════════════════════════════════════════

    /** Obtiene todos los empaques de un producto ordenados de menor a mayor */
    public function getEmpaques(int $producto_id): array {
        $stmt = $this->db->prepare("
            SELECT * FROM unidades_empaque
            WHERE producto_id = ? AND activo = 1
            ORDER BY orden ASC, cantidad ASC
        ");
        $stmt->execute([$producto_id]);
        return $stmt->fetchAll();
    }

    /**
     * Guarda los empaques de un producto.
     * Borra los anteriores y los recrea desde el array enviado por el formulario.
     * Estructura esperada:
     *   $empaques = [
     *     ['nombre'=>'Unidad', 'cantidad'=>1, 'codigo_barras'=>''],
     *     ['nombre'=>'Paquete', 'cantidad'=>23, 'codigo_barras'=>'779...'],
     *     ['nombre'=>'Caja máster', 'cantidad'=>230, 'codigo_barras'=>'780...'],
     *   ]
     */
    public function guardarEmpaques(int $producto_id, array $empaques): void {
        $stmt = $this->db->prepare("DELETE FROM unidades_empaque WHERE producto_id = ?");
        $stmt->execute([$producto_id]);

        if (empty($empaques)) return;

        $insert = $this->db->prepare("
            INSERT INTO unidades_empaque (producto_id, nombre, cantidad, es_base, codigo_barras, orden)
            VALUES (:producto_id, :nombre, :cantidad, :es_base, :codigo_barras, :orden)
        ");

        foreach ($empaques as $i => $emp) {
            if (empty(trim($emp['nombre'] ?? ''))) continue;
            $insert->execute([
                'producto_id'   => $producto_id,
                'nombre'        => trim($emp['nombre']),
                'cantidad'      => (float)($emp['cantidad'] ?? 1),
                'es_base'       => $i === 0 ? 1 : 0,
                'codigo_barras' => trim($emp['codigo_barras'] ?? '') ?: null,
                'orden'         => $i + 1,
            ]);
        }
    }

    /**
     * Convierte una cantidad de empaque a unidades base.
     * Ej: 3 cajas × 230 unidades/caja = 690 unidades base
     */
    public function convertirAUnidadesBase(int $empaque_id, float $cantidad): float {
        $stmt = $this->db->prepare("SELECT cantidad FROM unidades_empaque WHERE id = ?");
        $stmt->execute([$empaque_id]);
        $emp = $stmt->fetch();
        return $emp ? $cantidad * (float)$emp['cantidad'] : $cantidad;
    }

    /**
     * Desglosa el total de unidades base en la representación legible.
     * Ej: 695 unidades → 3 cajas + 0 paquetes + 5 unidades
     */
    public function desglosarEnEmpaques(int $producto_id, float $totalUnidades): array {
        $empaques = $this->getEmpaques($producto_id);
        if (empty($empaques)) {
            return [['nombre' => 'Unidades', 'cantidad' => $totalUnidades]];
        }

        // De mayor a menor para desglosar
        $empOrden = array_reverse($empaques);
        $desglose = [];
        $restante  = $totalUnidades;

        foreach ($empOrden as $emp) {
            if ((float)$emp['cantidad'] <= 1) continue;
            $cuantos = floor($restante / (float)$emp['cantidad']);
            if ($cuantos > 0) {
                $desglose[] = ['nombre' => $emp['nombre'], 'cantidad' => (int)$cuantos];
                $restante  -= $cuantos * (float)$emp['cantidad'];
            }
        }

        // Unidades sueltas
        if ($restante > 0) {
            $base = $empaques[0];
            $desglose[] = ['nombre' => $base['nombre'] ?? 'Unidades', 'cantidad' => (int)$restante];
        }

        return $desglose;
    }
}
