<?php
// ============================================================
// InvControl – Controlador: Gestión de Usuarios + Permisos
// ============================================================

class UsuarioController {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function handle(string $action, string $rol): void {
        Permisos::requerir('usuarios', Permisos::VER);
        match ($action) {
            'index'          => $this->index(),
            'crear'          => $this->formCrear(),
            'guardar'        => $this->guardar(),
            'editar'         => $this->formEditar(),
            'actualizar'     => $this->actualizar(),
            'password'       => $this->formPassword(),
            'cambiarPassword'=> $this->cambiarPassword(),
            'toggleActivo'   => $this->toggleActivo(),
            'permisos'       => $this->formPermisos(),
            'guardarPermisos'=> $this->guardarPermisos(),
            default          => $this->index(),
        };
    }

    private function index(): void {
        $stmt = $this->db->query("
            SELECT u.id, u.nombre, u.email, u.rol, u.activo, u.created_at
            FROM usuarios u ORDER BY u.nombre
        ");
        $usuarios = $stmt->fetchAll();

        // Obtener resumen de permisos de cada usuario
        $resumenPermisos = [];
        foreach ($usuarios as $u) {
            if ($u['rol'] === 'admin') {
                $resumenPermisos[$u['id']] = 'Acceso completo';
            } else {
                $perms = Permisos::getDeUsuario($u['id']);
                $activos = array_filter($perms, fn($n) => $n > 0);
                $resumenPermisos[$u['id']] = count($activos) . ' módulo(s) habilitados';
            }
        }
        require_once __DIR__ . '/../views/usuarios/index.php';
    }

    private function formCrear(): void {
        Permisos::requerir('usuarios', Permisos::EDITAR);
        $permisosDefault = Permisos::defaultsPorRol('operador');
        require_once __DIR__ . '/../views/usuarios/form.php';
    }

    private function guardar(): void {
        Permisos::requerir('usuarios', Permisos::EDITAR);

        $nombre   = trim($_POST['nombre']   ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = trim($_POST['password'] ?? '');
        $rol      = $_POST['rol'] ?? 'operador';

        if (empty($nombre) || empty($email) || empty($password)) {
            $_SESSION['error'] = 'Todos los campos son obligatorios.';
            header('Location: ' . APP_URL . '/?page=usuarios&action=crear'); exit;
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = 'El correo no es válido.';
            header('Location: ' . APP_URL . '/?page=usuarios&action=crear'); exit;
        }
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
            header('Location: ' . APP_URL . '/?page=usuarios&action=crear'); exit;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Ya existe un usuario con ese correo.';
            header('Location: ' . APP_URL . '/?page=usuarios&action=crear'); exit;
        }

        $hash = password_hash($password, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("INSERT INTO usuarios (nombre, email, password_hash, rol, activo) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$nombre, $email, $hash, $rol]);
        $nuevoId = $this->db->lastInsertId();

        // Guardar permisos
        $permisos = $_POST['permisos'] ?? [];
        // Si es admin, darle todo
        if ($rol === 'admin') {
            $permisos = array_fill_keys(array_keys(Permisos::MODULOS), Permisos::EDITAR);
        }
        Permisos::guardar((int)$nuevoId, $permisos);

        $_SESSION['success'] = "Usuario '{$nombre}' creado exitosamente con sus permisos.";
        header('Location: ' . APP_URL . '/?page=usuarios'); exit;
    }

    private function formEditar(): void {
        Permisos::requerir('usuarios', Permisos::EDITAR);
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare("SELECT * FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        if (!$usuario) {
            $_SESSION['error'] = 'Usuario no encontrado.';
            header('Location: ' . APP_URL . '/?page=usuarios'); exit;
        }
        $permisosDefault = Permisos::getDeUsuario($id);
        require_once __DIR__ . '/../views/usuarios/form.php';
    }

    private function actualizar(): void {
        Permisos::requerir('usuarios', Permisos::EDITAR);
        $id     = (int)($_POST['id'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $email  = trim($_POST['email']  ?? '');
        $rol    = $_POST['rol'] ?? 'operador';

        if (empty($nombre) || empty($email)) {
            $_SESSION['error'] = 'Nombre y correo son obligatorios.';
            header('Location: ' . APP_URL . '/?page=usuarios&action=editar&id=' . $id); exit;
        }

        $stmt = $this->db->prepare("SELECT COUNT(*) FROM usuarios WHERE email = ? AND id != ?");
        $stmt->execute([$email, $id]);
        if ($stmt->fetchColumn() > 0) {
            $_SESSION['error'] = 'Ese correo ya está en uso.';
            header('Location: ' . APP_URL . '/?page=usuarios&action=editar&id=' . $id); exit;
        }

        if ($id === (int)$_SESSION['usuario_id'] && $rol !== 'admin') {
            $_SESSION['error'] = 'No puedes cambiar tu propio rol.';
            header('Location: ' . APP_URL . '/?page=usuarios&action=editar&id=' . $id); exit;
        }

        $stmt = $this->db->prepare("UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?");
        $stmt->execute([$nombre, $email, $rol, $id]);

        // Actualizar permisos
        $permisos = $_POST['permisos'] ?? [];
        if ($rol === 'admin') {
            $permisos = array_fill_keys(array_keys(Permisos::MODULOS), Permisos::EDITAR);
        }
        Permisos::guardar($id, $permisos);

        $_SESSION['success'] = 'Usuario y permisos actualizados correctamente.';
        header('Location: ' . APP_URL . '/?page=usuarios'); exit;
    }

    private function formPassword(): void {
        Permisos::requerir('usuarios', Permisos::EDITAR);
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare("SELECT id, nombre, email FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        if (!$usuario) { header('Location: ' . APP_URL . '/?page=usuarios'); exit; }
        require_once __DIR__ . '/../views/usuarios/password.php';
    }

    private function cambiarPassword(): void {
        Permisos::requerir('usuarios', Permisos::EDITAR);
        $id          = (int)($_POST['id'] ?? 0);
        $newPassword = trim($_POST['new_password']      ?? '');
        $confirmar   = trim($_POST['confirmar_password'] ?? '');

        if (strlen($newPassword) < 6) {
            $_SESSION['error'] = 'La contraseña debe tener al menos 6 caracteres.';
            header('Location: ' . APP_URL . '/?page=usuarios&action=password&id=' . $id); exit;
        }
        if ($newPassword !== $confirmar) {
            $_SESSION['error'] = 'Las contraseñas no coinciden.';
            header('Location: ' . APP_URL . '/?page=usuarios&action=password&id=' . $id); exit;
        }

        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->db->prepare("UPDATE usuarios SET password_hash = ? WHERE id = ?");
        $stmt->execute([$hash, $id]);

        $_SESSION['success'] = 'Contraseña actualizada correctamente.';
        header('Location: ' . APP_URL . '/?page=usuarios'); exit;
    }

    private function toggleActivo(): void {
        Permisos::requerir('usuarios', Permisos::EDITAR);
        $id = (int)($_GET['id'] ?? 0);
        if ($id === (int)$_SESSION['usuario_id']) {
            $_SESSION['error'] = 'No puedes desactivar tu propia cuenta.';
            header('Location: ' . APP_URL . '/?page=usuarios'); exit;
        }
        $stmt = $this->db->prepare("SELECT activo, nombre FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        if (!$usuario) { header('Location: ' . APP_URL . '/?page=usuarios'); exit; }

        $nuevo = $usuario['activo'] ? 0 : 1;
        $stmt  = $this->db->prepare("UPDATE usuarios SET activo = ? WHERE id = ?");
        $stmt->execute([$nuevo, $id]);

        $accion = $nuevo ? 'activado' : 'desactivado';
        $_SESSION['success'] = "Usuario '{$usuario['nombre']}' {$accion} correctamente.";
        header('Location: ' . APP_URL . '/?page=usuarios'); exit;
    }

    // ── Formulario de permisos dedicado ───────────────────────
    private function formPermisos(): void {
        Permisos::requerir('usuarios', Permisos::EDITAR);
        $id = (int)($_GET['id'] ?? 0);
        $stmt = $this->db->prepare("SELECT id, nombre, email, rol FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        if (!$usuario) { header('Location: ' . APP_URL . '/?page=usuarios'); exit; }
        $permisosActuales = Permisos::getDeUsuario($id);
        require_once __DIR__ . '/../views/usuarios/permisos.php';
    }

    private function guardarPermisos(): void {
        Permisos::requerir('usuarios', Permisos::EDITAR);
        $id       = (int)($_POST['id'] ?? 0);
        $permisos = $_POST['permisos'] ?? [];

        $stmt = $this->db->prepare("SELECT nombre, rol FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        $usuario = $stmt->fetch();
        if (!$usuario) { header('Location: ' . APP_URL . '/?page=usuarios'); exit; }

        if ($usuario['rol'] === 'admin') {
            $permisos = array_fill_keys(array_keys(Permisos::MODULOS), Permisos::EDITAR);
        }

        Permisos::guardar($id, $permisos);
        $_SESSION['success'] = "Permisos de '{$usuario['nombre']}' actualizados correctamente.";
        header('Location: ' . APP_URL . '/?page=usuarios'); exit;
    }
}
