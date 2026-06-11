<?php
// ============================================================
// InvControl – Controlador: Órdenes de Despacho
// ============================================================

class DespachoController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function handle(string $action, string $rol): void {
        match ($action) {
            'index'     => $this->index(),
            'nueva'     => $this->formNueva(),
            'guardar'   => $this->guardar(),
            'ver'       => $this->ver(),
            'despachar' => $this->despachar($rol),
            'anular'    => $this->anular($rol),
            'imprimir'  => $this->imprimir(),
            default     => $this->index(),
        };
    }

    // ── Lista de órdenes ──────────────────────────────────────
    private function index(): void {
        $stmt = $this->db->query("
            SELECT od.*, u.nombre AS usuario_nombre,
                   COUNT(odd.id) AS total_productos
            FROM ordenes_despacho od
            JOIN usuarios u ON od.usuario_id = u.id
            LEFT JOIN ordenes_despacho_detalle odd ON od.id = odd.orden_id
            GROUP BY od.id
            ORDER BY od.fecha_creacion DESC
        ");
        $ordenes   = $stmt->fetchAll();
        $productos = (new Producto())->getAll();
        require_once __DIR__ . '/../views/despachos/index.php';
    }

    // ── Formulario nueva orden ────────────────────────────────
    private function formNueva(): void {
        $productos = (new Producto())->getAll();
        require_once __DIR__ . '/../views/despachos/form.php';
    }

    // ── Guardar nueva orden (SIN despachar aún) ───────────────
    private function guardar(): void {
        $cliente    = trim($_POST['cliente']    ?? '');
        $telefono   = trim($_POST['telefono']   ?? '');
        $direccion  = trim($_POST['direccion']  ?? '');
        $observacion= trim($_POST['observacion']?? '');
        $productos  = $_POST['productos']       ?? [];
        $cantidades = $_POST['cantidades']      ?? [];

        if (empty($cliente)) {
            $_SESSION['error'] = 'El nombre del cliente es obligatorio.';
            header('Location: ' . APP_URL . '/?page=despachos&action=nueva');
            exit;
        }

        // Filtrar productos con cantidad > 0
        $items = [];
        foreach ($productos as $i => $prodId) {
            $cant = (int)($cantidades[$i] ?? 0);
            if ($prodId && $cant > 0) {
                $items[] = ['producto_id' => (int)$prodId, 'cantidad' => $cant];
            }
        }

        if (empty($items)) {
            $_SESSION['error'] = 'Debes agregar al menos un producto a la orden.';
            header('Location: ' . APP_URL . '/?page=despachos&action=nueva');
            exit;
        }

        // Validar stock disponible
        $prodModel = new Producto();
        foreach ($items as $item) {
            $prod = $prodModel->getById($item['producto_id']);
            if (!$prod) continue;
            if ($item['cantidad'] > $prod['stock_actual']) {
                $_SESSION['error'] = "Stock insuficiente para '{$prod['nombre']}'. Disponible: {$prod['stock_actual']} unidades.";
                header('Location: ' . APP_URL . '/?page=despachos&action=nueva');
                exit;
            }
        }

        // Generar número consecutivo
        $numero = $this->siguienteNumero();

        $this->db->beginTransaction();
        try {
            // Insertar orden
            $stmt = $this->db->prepare("
                INSERT INTO ordenes_despacho
                    (numero, cliente, telefono, direccion, observacion, estado, usuario_id)
                VALUES (?, ?, ?, ?, ?, 'pendiente', ?)
            ");
            $stmt->execute([$numero, $cliente, $telefono, $direccion, $observacion, $_SESSION['usuario_id']]);
            $ordenId = $this->db->lastInsertId();

            // Insertar detalle
            $stmtD = $this->db->prepare("
                INSERT INTO ordenes_despacho_detalle
                    (orden_id, producto_id, cantidad, stock_antes)
                VALUES (?, ?, ?, ?)
            ");
            foreach ($items as $item) {
                $prod = $prodModel->getById($item['producto_id']);
                $stmtD->execute([$ordenId, $item['producto_id'], $item['cantidad'], $prod['stock_actual']]);
            }

            $this->db->commit();
            $_SESSION['success'] = "Orden {$numero} creada. Revísala y confirma el despacho cuando estés listo.";
            header('Location: ' . APP_URL . '/?page=despachos&action=ver&id=' . $ordenId);
        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = 'Error al crear la orden. Intenta de nuevo.';
            header('Location: ' . APP_URL . '/?page=despachos&action=nueva');
        }
        exit;
    }

    // ── Ver detalle de una orden ──────────────────────────────
    private function ver(): void {
        $id    = (int)($_GET['id'] ?? 0);
        $orden = $this->getOrdenCompleta($id);
        if (!$orden) {
            $_SESSION['error'] = 'Orden no encontrada.';
            header('Location: ' . APP_URL . '/?page=despachos');
            exit;
        }
        require_once __DIR__ . '/../views/despachos/ver.php';
    }

    // ── Confirmar despacho (descuenta el stock) ───────────────
    private function despachar(string $rol): void {
        $id    = (int)($_GET['id'] ?? 0);
        $orden = $this->getOrdenCompleta($id);

        if (!$orden || $orden['estado'] !== 'pendiente') {
            $_SESSION['error'] = 'La orden no existe o ya fue procesada.';
            header('Location: ' . APP_URL . '/?page=despachos');
            exit;
        }

        $prodModel = new Producto();
        $movModel  = new Movimiento();

        $this->db->beginTransaction();
        try {
            // Verificar stock actualizado antes de despachar
            foreach ($orden['detalle'] as $item) {
                $prod = $prodModel->getById($item['producto_id']);
                if ($item['cantidad'] > $prod['stock_actual']) {
                    throw new Exception("Stock insuficiente para '{$prod['nombre']}'. Disponible: {$prod['stock_actual']}");
                }
            }

            // Descontar stock y registrar movimientos
            foreach ($orden['detalle'] as $item) {
                $prod       = $prodModel->getById($item['producto_id']);
                $nuevoStock = $prod['stock_actual'] - $item['cantidad'];

                $prodModel->updateStock($item['producto_id'], $nuevoStock);

                $movModel->registrar([
                    'producto_id'      => $item['producto_id'],
                    'usuario_id'       => $_SESSION['usuario_id'],
                    'tipo'             => 'salida',
                    'cantidad'         => $item['cantidad'],
                    'stock_resultante' => $nuevoStock,
                    'observacion'      => "Orden de Despacho {$orden['numero']} – Cliente: {$orden['cliente']}",
                ]);
            }

            // Actualizar estado de la orden
            $stmt = $this->db->prepare("
                UPDATE ordenes_despacho
                SET estado = 'despachado', fecha_despacho = NOW()
                WHERE id = ?
            ");
            $stmt->execute([$id]);

            $this->db->commit();
            $_SESSION['success'] = "Orden {$orden['numero']} despachada exitosamente. Stock actualizado.";
            header('Location: ' . APP_URL . '/?page=despachos&action=ver&id=' . $id);

        } catch (Exception $e) {
            $this->db->rollBack();
            $_SESSION['error'] = $e->getMessage();
            header('Location: ' . APP_URL . '/?page=despachos&action=ver&id=' . $id);
        }
        exit;
    }

    // ── Anular orden ──────────────────────────────────────────
    private function anular(string $rol): void {
        if ($rol !== 'admin') {
            $_SESSION['error'] = 'Solo el administrador puede anular órdenes.';
            header('Location: ' . APP_URL . '/?page=despachos');
            exit;
        }
        $id    = (int)($_GET['id'] ?? 0);
        $orden = $this->getOrdenCompleta($id);

        if (!$orden || $orden['estado'] === 'anulado') {
            $_SESSION['error'] = 'La orden no existe o ya fue anulada.';
            header('Location: ' . APP_URL . '/?page=despachos');
            exit;
        }

        $stmt = $this->db->prepare("UPDATE ordenes_despacho SET estado='anulado' WHERE id=?");
        $stmt->execute([$id]);
        $_SESSION['success'] = "Orden {$orden['numero']} anulada.";
        header('Location: ' . APP_URL . '/?page=despachos');
        exit;
    }

    // ── Imprimir orden ────────────────────────────────────────
    private function imprimir(): void {
        $id    = (int)($_GET['id'] ?? 0);
        $orden = $this->getOrdenCompleta($id);
        if (!$orden) {
            header('Location: ' . APP_URL . '/?page=despachos');
            exit;
        }
        require_once __DIR__ . '/../views/despachos/imprimir.php';
    }

    // ── Helpers ───────────────────────────────────────────────
    private function siguienteNumero(): string {
        $stmt = $this->db->query("SELECT numero FROM ordenes_despacho ORDER BY id DESC LIMIT 1");
        $ultimo = $stmt->fetchColumn();
        if ($ultimo) {
            $num = ((int)substr($ultimo, 3)) + 1;
        } else {
            $num = 1;
        }
        return 'OD-' . str_pad($num, 3, '0', STR_PAD_LEFT);
    }

    private function getOrdenCompleta(int $id): array|false {
        $stmt = $this->db->prepare("
            SELECT od.*, u.nombre AS usuario_nombre
            FROM ordenes_despacho od
            JOIN usuarios u ON od.usuario_id = u.id
            WHERE od.id = ?
        ");
        $stmt->execute([$id]);
        $orden = $stmt->fetch();
        if (!$orden) return false;

        $stmt2 = $this->db->prepare("
            SELECT odd.*, p.nombre AS producto_nombre, p.codigo AS producto_codigo,
                   c.nombre AS categoria_nombre
            FROM ordenes_despacho_detalle odd
            JOIN productos p ON odd.producto_id = p.id
            LEFT JOIN categorias c ON p.categoria_id = c.id
            WHERE odd.orden_id = ?
        ");
        $stmt2->execute([$id]);
        $orden['detalle'] = $stmt2->fetchAll();
        return $orden;
    }
}
