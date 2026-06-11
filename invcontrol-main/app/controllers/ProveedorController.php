<?php
// ============================================================
// InvControl – Controlador: Proveedores
// ============================================================
class ProveedorController {
    private Proveedor $model;
    public function __construct() { $this->model = new Proveedor(); }

    public function handle(string $action, string $rol): void {
    match ($action) {
        'index'    => $this->index(),
        'guardar'  => $this->guardar(),
        'eliminar' => $this->eliminar(),
        default    => $this->index(),
    };
    }


    private function index(): void {
        $proveedores = $this->model->getAll();
        require_once __DIR__ . '/../views/proveedores/index.php';
    }

    private function guardar(): void {
        $id = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        if (empty($nombre)) { $_SESSION['error'] = 'El nombre es obligatorio.'; header('Location: ' . APP_URL . '/?page=proveedores'); exit; }
        $data = ['nombre' => $nombre, 'contacto' => trim($_POST['contacto'] ?? ''), 'telefono' => trim($_POST['telefono'] ?? ''), 'email' => trim($_POST['email'] ?? '')];
        if ($id > 0) { $this->model->update($id, $data); $_SESSION['success'] = 'Proveedor actualizado.'; }
        else         { $this->model->create($data);       $_SESSION['success'] = 'Proveedor creado.'; }
        header('Location: ' . APP_URL . '/?page=proveedores'); exit;
    }

    private function eliminar(): void {
        $id = (int)($_GET['id'] ?? 0);
        if (!$this->model->delete($id)) { $_SESSION['error'] = 'No se puede eliminar: tiene productos asociados.'; }
        else { $_SESSION['success'] = 'Proveedor eliminado.'; }
        header('Location: ' . APP_URL . '/?page=proveedores'); exit;
    }
}
