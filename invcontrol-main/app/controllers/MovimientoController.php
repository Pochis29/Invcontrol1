<?php
// ============================================================
// InvControl – Controlador: Movimientos
// ============================================================

class MovimientoController {
    private Movimiento $model;
    private Producto   $prodModel;

    public function __construct() {
        $this->model     = new Movimiento();
        $this->prodModel = new Producto();
    }

    public function handle(string $action, string $rol): void {
        match ($action) {
            'index'    => $this->index(),
            'entrada'  => $this->formMovimiento('entrada'),
            'salida'   => $this->formMovimiento('salida'),
            'guardar'  => $this->guardar(),
            default    => $this->index(),
        };
    }

    private function index(): void {
        $movimientos = $this->model->getByFecha(
            $_GET['desde'] ?? date('Y-m-01'),
            $_GET['hasta'] ?? date('Y-m-d')
        );
        require_once __DIR__ . '/../views/movimientos/index.php';
    }

    private function formMovimiento(string $tipo): void {
        $productos = $this->prodModel->getAll();
        require_once __DIR__ . '/../views/movimientos/form.php';
    }

    private function guardar(): void {
        $productoId  = (int)($_POST['producto_id'] ?? 0);
        $tipo        = $_POST['tipo'] ?? '';
        $cantidad    = (int)($_POST['cantidad'] ?? 0);
        $observacion = trim($_POST['observacion'] ?? '');

        if (!$productoId || !in_array($tipo, ['entrada','salida','ajuste']) || $cantidad <= 0) {
            $_SESSION['error'] = 'Datos inválidos. Verifique el producto, tipo y cantidad.';
            header('Location: ' . APP_URL . '/?page=movimientos&action=' . $tipo);
            exit;
        }

        $producto = $this->prodModel->getById($productoId);
        if (!$producto) {
            $_SESSION['error'] = 'Producto no encontrado.';
            header('Location: ' . APP_URL . '/?page=movimientos');
            exit;
        }

        // Calcular nuevo stock
        $stockActual = $producto['stock_actual'];
        if ($tipo === 'entrada' || $tipo === 'ajuste') {
            $nuevoStock = $stockActual + $cantidad;
        } else { // salida
            if ($cantidad > $stockActual) {
                $_SESSION['error'] = "Stock insuficiente. Stock disponible: {$stockActual} unidades.";
                header('Location: ' . APP_URL . '/?page=movimientos&action=salida');
                exit;
            }
            $nuevoStock = $stockActual - $cantidad;
        }

        // Registrar movimiento y actualizar stock en transacción
        $pdo = Database::getConnection();
        $pdo->beginTransaction();
        try {
            $this->model->registrar([
                'producto_id'      => $productoId,
                'usuario_id'       => $_SESSION['usuario_id'],
                'tipo'             => $tipo,
                'cantidad'         => $cantidad,
                'stock_resultante' => $nuevoStock,
                'observacion'      => $observacion,
            ]);
            $this->prodModel->updateStock($productoId, $nuevoStock);
            $pdo->commit();
            $_SESSION['success'] = ucfirst($tipo) . ' registrada correctamente. Nuevo stock: ' . $nuevoStock;
        } catch (Exception $e) {
            $pdo->rollBack();
            $_SESSION['error'] = 'Error al registrar el movimiento. Intente de nuevo.';
        }

        header('Location: ' . APP_URL . '/?page=movimientos');
        exit;
    }
}
