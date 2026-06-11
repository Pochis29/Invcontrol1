<?php
// ============================================================
// InvControl – Controlador: Reportes (con Kardex completo)
// ============================================================
class ReporteController {

    public function handle(string $action, string $rol): void {
        if ($rol !== 'admin') {
            $_SESSION['error'] = 'Acceso no autorizado.';
            header('Location: ' . APP_URL . '/');
            exit;
        }
        match ($action) {
            'existencias'  => $this->existencias(),
            'movimientos'  => $this->movimientos(),
            'kardex'       => $this->kardex(),
            default        => $this->existencias(),
        };
    }

    // ── Reporte de existencias ────────────────────────────────
    private function existencias(): void {
        $productos = (new Producto())->getAll();
        require_once __DIR__ . '/../views/reportes/existencias.php';
    }

    // ── Reporte de movimientos por fecha ──────────────────────
    private function movimientos(): void {
        $desde = $_GET['desde'] ?? date('Y-m-01');
        $hasta = $_GET['hasta'] ?? date('Y-m-d');
        $movimientos = (new Movimiento())->getByFecha($desde, $hasta);
        $productos   = (new Producto())->getAll();
        require_once __DIR__ . '/../views/reportes/movimientos.php';
    }

    // ── Reporte Kardex completo ───────────────────────────────
    private function kardex(): void {
        $pdo = Database::getConnection();

        // Filtros recibidos
        $desde      = $_GET['desde']       ?? date('Y-m-01');
        $hasta      = $_GET['hasta']       ?? date('Y-m-d');
        $productoId = (int)($_GET['producto_id'] ?? 0);
        $categoriaId= (int)($_GET['categoria_id'] ?? 0);
        $tipo       = $_GET['tipo']        ?? '';

        // Construir consulta dinámica
        $where  = ["m.fecha BETWEEN :desde AND :hasta"];
        $params = [
            ':desde' => $desde . ' 00:00:00',
            ':hasta' => $hasta . ' 23:59:59',
        ];

        if ($productoId > 0) {
            $where[]  = "m.producto_id = :producto_id";
            $params[':producto_id'] = $productoId;
        }

        if ($categoriaId > 0) {
            $where[]  = "p.categoria_id = :categoria_id";
            $params[':categoria_id'] = $categoriaId;
        }

        if (in_array($tipo, ['entrada', 'salida', 'ajuste'])) {
            $where[]  = "m.tipo = :tipo";
            $params[':tipo'] = $tipo;
        }

        $whereSQL = implode(' AND ', $where);

        $stmt = $pdo->prepare("
            SELECT
                m.id,
                m.fecha,
                m.tipo,
                m.cantidad,
                m.stock_resultante,
                m.observacion,
                p.codigo        AS producto_codigo,
                p.nombre        AS producto_nombre,
                c.nombre        AS categoria_nombre,
                u.nombre        AS usuario_nombre
            FROM movimientos m
            JOIN productos  p ON m.producto_id = p.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            JOIN usuarios   u ON m.usuario_id  = u.id
            WHERE {$whereSQL}
            ORDER BY m.fecha DESC
        ");
        $stmt->execute($params);
        $movimientos = $stmt->fetchAll();

        // Totales para el resumen
        $totalEntradas = 0;
        $totalSalidas  = 0;
        $totalAjustes  = 0;
        foreach ($movimientos as $m) {
            if ($m['tipo'] === 'entrada') $totalEntradas += $m['cantidad'];
            elseif ($m['tipo'] === 'salida') $totalSalidas  += $m['cantidad'];
            else $totalAjustes += $m['cantidad'];
        }

        // Datos para la gráfica (agrupados por fecha)
        $graficaSQL = "
            SELECT
                DATE(m.fecha) AS dia,
                SUM(CASE WHEN m.tipo='entrada' THEN m.cantidad ELSE 0 END) AS entradas,
                SUM(CASE WHEN m.tipo='salida'  THEN m.cantidad ELSE 0 END) AS salidas
            FROM movimientos m
            JOIN productos p ON m.producto_id = p.id
            WHERE {$whereSQL}
            GROUP BY DATE(m.fecha)
            ORDER BY dia ASC
            LIMIT 30
        ";
        $stmtG = $pdo->prepare($graficaSQL);
        $stmtG->execute($params);
        $graficaData = $stmtG->fetchAll();

        // Top 5 productos con más movimientos
        $topSQL = "
            SELECT
                p.nombre AS producto_nombre,
                p.codigo AS producto_codigo,
                COUNT(m.id) AS total_movimientos,
                SUM(CASE WHEN m.tipo='entrada' THEN m.cantidad ELSE 0 END) AS total_entradas,
                SUM(CASE WHEN m.tipo='salida'  THEN m.cantidad ELSE 0 END) AS total_salidas
            FROM movimientos m
            JOIN productos p ON m.producto_id = p.id
            WHERE {$whereSQL}
            GROUP BY m.producto_id
            ORDER BY total_movimientos DESC
            LIMIT 5
        ";
        $stmtT = $pdo->prepare($topSQL);
        $stmtT->execute($params);
        $topProductos = $stmtT->fetchAll();

        // Listas para filtros
        $productos   = (new Producto())->getAll();
        $categorias  = (new Categoria())->getAll();

        require_once __DIR__ . '/../views/reportes/kardex.php';
    }
}
