<?php
// ============================================================
// InvControl – Controlador: Scanner de Código de Barras
// ============================================================

class ScannerController {
    private Producto   $prodModel;
    private Movimiento $movModel;

    public function __construct() {
        $this->prodModel = new Producto();
        $this->movModel  = new Movimiento();
    }

    public function handle(string $action, string $rol): void {
        match ($action) {
            'entrada'      => $this->scannerView('entrada'),
            'salida'       => $this->scannerView('salida'),
            'buscar'       => $this->buscarProducto(),
            'registrar'    => $this->registrarMovimiento(),
            'etiquetas'    => $this->etiquetas($rol),
            'imprimir'     => $this->imprimirEtiqueta(),
            default        => $this->scannerView('entrada'),
        };
    }

    // ── Vista del scanner ─────────────────────────────────────
    private function scannerView(string $tipo): void {
        require_once __DIR__ . '/../views/scanner/index.php';
    }

    // ── AJAX: buscar producto por código escaneado ────────────
    private function buscarProducto(): void {
        header('Content-Type: application/json');
        $codigo = trim($_POST['codigo'] ?? $_GET['codigo'] ?? '');

        if (empty($codigo)) {
            echo json_encode(['ok' => false, 'msg' => 'Código vacío']);
            exit;
        }

        // Buscar por código exacto o código de barras del proveedor
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("
            SELECT p.*, c.nombre AS categoria_nombre, pr.nombre AS proveedor_nombre
            FROM productos p
            LEFT JOIN categorias c  ON p.categoria_id = c.id
            LEFT JOIN proveedores pr ON p.proveedor_id = pr.id
            WHERE (p.codigo = ? OR p.codigo_barras = ?) AND p.activo = 1
            LIMIT 1
        ");
        $stmt->execute([$codigo, $codigo]);
        $producto = $stmt->fetch();

        if ($producto) {
            echo json_encode([
                'ok'       => true,
                'producto' => $producto,
            ]); 
        } else {
            echo json_encode([
                'ok'  => false,
                'msg' => "Producto no encontrado para el código: <strong>{$codigo}</strong>",
            ]);
        }
        exit;
    }

    // ── Registrar movimiento desde scanner ────────────────────
    private function registrarMovimiento(): void {
        header('Content-Type: application/json');

        $productoId  = (int)($_POST['producto_id'] ?? 0);
        $tipo        = $_POST['tipo'] ?? '';
        $cantidad    = (int)($_POST['cantidad'] ?? 0);
        $observacion = trim($_POST['observacion'] ?? '');

        if (!$productoId || !in_array($tipo, ['entrada','salida']) || $cantidad <= 0) {
            echo json_encode(['ok' => false, 'msg' => 'Datos inválidos.']);
            exit;
        }

        $producto = $this->prodModel->getById($productoId);
        if (!$producto) {
            echo json_encode(['ok' => false, 'msg' => 'Producto no encontrado.']);
            exit;
        }

        $stockActual = $producto['stock_actual'];

        if ($tipo === 'salida' && $cantidad > $stockActual) {
            echo json_encode([
                'ok'  => false,
                'msg' => "Stock insuficiente. Disponible: <strong>{$stockActual}</strong> unidades.",
            ]);
            exit;
        }

        $nuevoStock = $tipo === 'entrada'
            ? $stockActual + $cantidad
            : $stockActual - $cantidad;

        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $this->movModel->registrar([
                'producto_id'      => $productoId,
                'usuario_id'       => $_SESSION['usuario_id'],
                'tipo'             => $tipo,
                'cantidad'         => $cantidad,
                'stock_resultante' => $nuevoStock,
                'observacion'      => $observacion ?: "Registro vía scanner – {$tipo}",
            ]);
            $this->prodModel->updateStock($productoId, $nuevoStock);
            $pdo->commit();

            echo json_encode([
                'ok'          => true,
                'msg'         => "✅ {$tipo} registrada correctamente",
                'nuevo_stock' => $nuevoStock,
                'producto'    => $producto['nombre'],
            ]);
        } catch (Exception $e) {
            $pdo->rollBack();
            echo json_encode(['ok' => false, 'msg' => 'Error al registrar. Intente de nuevo.']);
        }
        exit;
    }

    // ── Vista de etiquetas ────────────────────────────────────
    private function etiquetas(string $rol): void {
        if ($rol !== 'admin') {
            $_SESSION['error'] = 'Acceso no autorizado.';
            header('Location: ' . APP_URL . '/');
            exit;
        }
        $productos = $this->prodModel->getAll();
        require_once __DIR__ . '/../views/etiquetas/index.php';
    }

    // ── Imprimir etiqueta(s) — soporta ?id=X y ?ids=1,2,3 ────
    private function imprimirEtiqueta(): void {
        $pdo = Database::getConnection();

        if (!empty($_GET['ids'])) {
            $ids = array_filter(array_map('intval', explode(',', $_GET['ids'])));
            if (empty($ids)) {
                header('Location: ' . APP_URL . '/?page=scanner&action=etiquetas');
                exit;
            }
            $marks     = implode(',', array_fill(0, count($ids), '?'));
            $stmt      = $pdo->prepare("SELECT * FROM productos WHERE id IN ($marks) AND activo = 1 ORDER BY nombre");
            $stmt->execute($ids);
            $productos = $stmt->fetchAll();
        } else {
            $id    = (int)($_GET['id'] ?? 0);
            $stmt  = $pdo->prepare("SELECT * FROM productos WHERE id = ? AND activo = 1");
            $stmt->execute([$id]);
            $productos = $stmt->fetchAll();
        }

        if (empty($productos)) {
            header('Location: ' . APP_URL . '/?page=scanner&action=etiquetas');
            exit;
        }

        require_once __DIR__ . '/../views/etiquetas/imprimir.php';
    }
}