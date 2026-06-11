<?php
// ============================================================
// InvControl – Router principal v8 (con sistema de permisos)
// ============================================================

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/app/models/Usuario.php';
require_once __DIR__ . '/app/models/Producto.php';
require_once __DIR__ . '/app/models/Categoria.php';
require_once __DIR__ . '/app/models/Proveedor.php';
require_once __DIR__ . '/app/models/Movimiento.php';
require_once __DIR__ . '/app/helpers/Permisos.php';
require_once __DIR__ . '/app/controllers/AuthController.php';
require_once __DIR__ . '/app/controllers/ProductoController.php';
require_once __DIR__ . '/app/controllers/MovimientoController.php';
require_once __DIR__ . '/app/controllers/CategoriaController.php';
require_once __DIR__ . '/app/controllers/ProveedorController.php';
require_once __DIR__ . '/app/controllers/ReporteController.php';
require_once __DIR__ . '/app/controllers/ScannerController.php';
require_once __DIR__ . '/app/controllers/UsuarioController.php';
require_once __DIR__ . '/app/controllers/DespachoController.php';

session_name(SESSION_NAME ?? 'invcontrol_session');
session_start();

$page   = $_GET['page']   ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

if ($page === 'auth') {
    $ctrl = new AuthController();
    match ($action) {
        'login'  => $ctrl->login(),
        'logout' => $ctrl->logout(),
        default  => $ctrl->login(),
    };
    exit;
}

if (empty($_SESSION['usuario_id'])) {
    header('Location: ' . APP_URL . '/?page=auth&action=login');
    exit;
}

$rol = $_SESSION['rol'] ?? 'operador';

// Cargar permisos del usuario en sesión
Permisos::cargar();

match ($page) {
    'dashboard'   => (new AuthController())->dashboard(),
    'productos'   => (new ProductoController())->handle($action, $rol),
    'movimientos' => (new MovimientoController())->handle($action, $rol),
    'categorias'  => (new CategoriaController())->handle($action, $rol),
    'proveedores' => (new ProveedorController())->handle($action, $rol),
    'reportes'    => (new ReporteController())->handle($action, $rol),
    'scanner'     => (new ScannerController())->handle($action, $rol),
    'usuarios'    => (new UsuarioController())->handle($action, $rol),
    'despachos'   => (new DespachoController())->handle($action, $rol),
    default       => (new AuthController())->dashboard(),
};
