<?php
// ============================================================
// InvControl – Controlador: Autenticación
// ============================================================

class AuthController {
    private Usuario $model;

    public function __construct() {
        $this->model = new Usuario();
    }

    public function login(): void {
        if (!empty($_SESSION['usuario_id'])) {
            header('Location: ' . APP_URL . '/');
            exit;
        }

        $error = '';

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email    = trim($_POST['email']    ?? '');
            $password = trim($_POST['password'] ?? '');

            if (empty($email) || empty($password)) {
                $error = 'Complete todos los campos.';
            } else {
                $usuario = $this->model->login($email, $password);
                if ($usuario) {
                    session_regenerate_id(true);
                    $_SESSION['usuario_id'] = $usuario['id'];
                    $_SESSION['nombre']     = $usuario['nombre'];
                    $_SESSION['email']      = $usuario['email'];
                    $_SESSION['rol']        = $usuario['rol'];
                    header('Location: ' . APP_URL . '/');
                    exit;
                } else {
                    $error = 'Usuario o contraseña incorrectos.';
                }
            }
        }

        require_once __DIR__ . '/../views/auth/login.php';
    }

    public function logout(): void {
        session_destroy();
        header('Location: ' . APP_URL . '/?page=auth&action=login');
        exit;
    }

    public function dashboard(): void {
        $productoModel   = new Producto();
        $movimientoModel = new Movimiento();

        $totalProductos   = count($productoModel->getAll());
        $bajoStock        = count($productoModel->getProductosBajoStock());
        $resumenHoy       = $movimientoModel->getResumenHoy();
        $alertas          = $productoModel->getProductosBajoStock();

        require_once __DIR__ . '/../views/dashboard/index.php';
    }
}
