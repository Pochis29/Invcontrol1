<?php
// ============================================================
// InvControl – Controlador: Categorias (con prefijo)
// ============================================================
class CategoriaController {
    private Categoria $model;
    public function __construct() { $this->model = new Categoria(); }

    public function handle(string $action, string $rol): void {
        if ($rol !== 'admin') {
            $_SESSION['error'] = 'Acceso no autorizado.';
            header('Location: ' . APP_URL . '/'); exit;
        }
        match ($action) {
            'index'          => $this->index(),
            'guardar'        => $this->guardar(),
            'eliminar'       => $this->eliminar(),
            'siguienteCodigo'=> $this->ajaxSiguienteCodigo(),
            default          => $this->index(),
        };
    }

    private function index(): void {
        $categorias = $this->model->getAll();
        require_once __DIR__ . '/../views/categorias/index.php';
    }

    private function guardar(): void {
        $id      = (int)($_POST['id'] ?? 0);
        $nombre  = trim($_POST['nombre']  ?? '');
        $prefijo = strtoupper(trim($_POST['prefijo'] ?? ''));

        if (empty($nombre)) {
            $_SESSION['error'] = 'El nombre es obligatorio.';
            header('Location: ' . APP_URL . '/?page=categorias'); exit;
        }

        $data = [
            'nombre'      => $nombre,
            'prefijo'     => $prefijo ?: null,
            'descripcion' => trim($_POST['descripcion'] ?? ''),
        ];

        if ($id > 0) {
            $this->model->update($id, $data);
            $_SESSION['success'] = 'Categoría actualizada.';
        } else {
            $this->model->create($data);
            $_SESSION['success'] = 'Categoría creada.';
        }
        header('Location: ' . APP_URL . '/?page=categorias'); exit;
    }

    private function eliminar(): void {
        $id = (int)($_GET['id'] ?? 0);
        if (!$this->model->delete($id)) {
            $_SESSION['error'] = 'No se puede eliminar: tiene productos asociados.';
        } else {
            $_SESSION['success'] = 'Categoría eliminada.';
        }
        header('Location: ' . APP_URL . '/?page=categorias'); exit;
    }

    // ── AJAX: devuelve el siguiente código para una categoría ──
    public function ajaxSiguienteCodigo(): void {
        header('Content-Type: application/json');
        $categoriaId = (int)($_GET['categoria_id'] ?? 0);
        if (!$categoriaId) {
            echo json_encode(['codigo' => '']);
            exit;
        }
        $codigo = $this->model->siguienteCodigo($categoriaId);
        echo json_encode(['codigo' => $codigo]);
        exit;
    }
}
