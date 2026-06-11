<?php
// ============================================================
// InvControl – Controlador: Productos (con empaques WMS)
// ============================================================

class ProductoController {
    private Producto  $model;
    private Categoria $catModel;
    private Proveedor $provModel;

    public function __construct() {
        $this->model     = new Producto();
        $this->catModel  = new Categoria();
        $this->provModel = new Proveedor();
    }

    public function handle(string $action, string $rol): void {
        match ($action) {
            'index'           => $this->index(),
            'nuevo'           => $this->nuevo($rol),
            'guardar'         => $this->guardar($rol),
            'editar'          => $this->editar($rol),
            'actualizar'      => $this->actualizar($rol),
            'eliminar'        => $this->eliminar($rol),
            'kardex'          => $this->kardex(),
            'buscarPorCodigo' => $this->buscarPorCodigo(),
            'empaques'        => $this->verEmpaques(),
            default           => $this->index(),
        };
    }

    private function index(): void {
        $productos = $this->model->getAll();
        require_once __DIR__ . '/../views/productos/index.php';
    }

    private function nuevo(string $rol): void {
        if ($rol !== 'admin') { $this->sinPermiso(); return; }
        $categorias  = $this->catModel->getAll();
        $proveedores = $this->provModel->getAll();
        $producto    = ['codigo_barras' => $_GET['codigo_barras'] ?? '', 'unidad_base' => 'Unidad'];
        $empaques    = [];
        require_once __DIR__ . '/../views/productos/form.php';
    }

    private function guardar(string $rol): void {
        if ($rol !== 'admin') { $this->sinPermiso(); return; }

        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');

        if (empty($codigo) || empty($nombre)) {
            $_SESSION['error'] = 'Código y nombre son obligatorios.';
            header('Location: ' . APP_URL . '/?page=productos&action=nuevo');
            exit;
        }

        if ($this->model->codigoExiste($codigo)) {
            $_SESSION['error'] = 'El código ya está en uso.';
            header('Location: ' . APP_URL . '/?page=productos&action=nuevo');
            exit;
        }

        // Crear producto y obtener el ID
        $productoId = $this->model->create([
            'codigo'        => $codigo,
            'codigo_barras' => trim($_POST['codigo_barras'] ?? '') ?: null,
            'unidad_base'   => trim($_POST['unidad_base'] ?? 'Unidad') ?: 'Unidad',
            'nombre'        => $nombre,
            'descripcion'   => trim($_POST['descripcion']  ?? ''),
            'categoria_id'  => $_POST['categoria_id']  ?: null,
            'proveedor_id'  => $_POST['proveedor_id']  ?: null,
            'stock_actual'  => (int)($_POST['stock_actual'] ?? 0),
            'stock_minimo'  => (int)($_POST['stock_minimo'] ?? 5),
        ]);

        // Guardar empaques si se enviaron
        $empaques = $this->parsearEmpaques();
        if (!empty($empaques)) {
            $this->model->guardarEmpaques($productoId, $empaques);
        }

        $_SESSION['success'] = 'Producto registrado exitosamente.';
        header('Location: ' . APP_URL . '/?page=productos');
        exit;
    }

    private function editar(string $rol): void {
        if ($rol !== 'admin') { $this->sinPermiso(); return; }
        $id          = (int)($_GET['id'] ?? 0);
        $producto    = $this->model->getById($id);
        $categorias  = $this->catModel->getAll();
        $proveedores = $this->provModel->getAll();
        $empaques    = $this->model->getEmpaques($id);
        if (!$producto) { header('Location: ' . APP_URL . '/?page=productos'); exit; }
        require_once __DIR__ . '/../views/productos/form.php';
    }

    private function actualizar(string $rol): void {
        if ($rol !== 'admin') { $this->sinPermiso(); return; }
        $id     = (int)($_POST['id'] ?? 0);
        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');

        if (empty($codigo) || empty($nombre)) {
            $_SESSION['error'] = 'Código y nombre son obligatorios.';
            header('Location: ' . APP_URL . '/?page=productos&action=editar&id=' . $id);
            exit;
        }

        if ($this->model->codigoExiste($codigo, $id)) {
            $_SESSION['error'] = 'El código ya está en uso por otro producto.';
            header('Location: ' . APP_URL . '/?page=productos&action=editar&id=' . $id);
            exit;
        }

        $this->model->update($id, [
            'codigo'        => $codigo,
            'codigo_barras' => trim($_POST['codigo_barras'] ?? '') ?: null,
            'unidad_base'   => trim($_POST['unidad_base'] ?? 'Unidad') ?: 'Unidad',
            'nombre'        => $nombre,
            'descripcion'   => trim($_POST['descripcion'] ?? ''),
            'categoria_id'  => $_POST['categoria_id'] ?: null,
            'proveedor_id'  => $_POST['proveedor_id'] ?: null,
            'stock_minimo'  => (int)($_POST['stock_minimo'] ?? 5),
        ]);

        // Actualizar empaques
        $empaques = $this->parsearEmpaques();
        $this->model->guardarEmpaques($id, $empaques);

        $_SESSION['success'] = 'Producto actualizado correctamente.';
        header('Location: ' . APP_URL . '/?page=productos');
        exit;
    }

    private function eliminar(string $rol): void {
        if ($rol !== 'admin') { $this->sinPermiso(); return; }
        $id = (int)($_GET['id'] ?? 0);
        $this->model->delete($id);
        $_SESSION['success'] = 'Producto eliminado correctamente.';
        header('Location: ' . APP_URL . '/?page=productos');
        exit;
    }

    private function kardex(): void {
        $id          = (int)($_GET['id'] ?? 0);
        $producto    = $this->model->getById($id);
        $movModel    = new Movimiento();
        $movimientos = $movModel->getKardex($id);
        require_once __DIR__ . '/../views/productos/kardex.php';
    }

    /** Endpoint AJAX: busca producto por código de barras (producto o empaque) o nombre */
    private function buscarPorCodigo(): void {
        header('Content-Type: application/json');
        $termino = trim($_GET['q'] ?? '');

        if (empty($termino)) { echo json_encode(['encontrado' => false]); exit; }

        // 1. Buscar por código de barras (producto o empaque)
        $producto = $this->model->getByCodigoBarras($termino);

        // 2. Si no, buscar por código interno exacto
        if (!$producto) {
            foreach ($this->model->getAll() as $p) {
                if (strtoupper($p['codigo']) === strtoupper($termino)) {
                    $producto = $p;
                    break;
                }
            }
        }

        if ($producto) {
            // Obtener empaques para mostrar en el formulario de movimiento
            $empaques = $this->model->getEmpaques((int)$producto['id']);

            echo json_encode([
                'encontrado'      => true,
                'id'              => $producto['id'],
                'nombre'          => $producto['nombre'],
                'codigo'          => $producto['codigo'],
                'stock_actual'    => $producto['stock_actual'],
                'unidad_base'     => $producto['unidad_base'] ?? 'Unidad',
                'empaque_id'      => $producto['empaque_id']      ?? null,
                'empaque_nombre'  => $producto['empaque_nombre']  ?? null,
                'empaque_cantidad'=> $producto['empaque_cantidad'] ?? null,
                'empaques'        => $empaques,
            ]);
        } else {
            echo json_encode(['encontrado' => false]);
        }
        exit;
    }

    /** Vista de empaques de un producto */
    private function verEmpaques(): void {
        $id       = (int)($_GET['id'] ?? 0);
        $producto = $this->model->getById($id);
        $empaques = $this->model->getEmpaques($id);
        if (!$producto) { header('Location: ' . APP_URL . '/?page=productos'); exit; }
        require_once __DIR__ . '/../views/productos/empaques.php';
    }

    /** Parsea el array de empaques enviado desde el formulario */
    private function parsearEmpaques(): array {
        $nombres       = $_POST['emp_nombre']        ?? [];
        $cantidades    = $_POST['emp_cantidad']       ?? [];
        $codigosBarras = $_POST['emp_codigo_barras']  ?? [];

        $empaques = [];
        foreach ($nombres as $i => $nombre) {
            if (empty(trim($nombre))) continue;
            $empaques[] = [
                'nombre'        => trim($nombre),
                'cantidad'      => (float)($cantidades[$i] ?? 1),
                'codigo_barras' => trim($codigosBarras[$i] ?? ''),
            ];
        }
        return $empaques;
    }

    private function sinPermiso(): void {
        $_SESSION['error'] = 'No tiene permisos para realizar esta acción.';
        header('Location: ' . APP_URL . '/');
        exit;
    }
}
