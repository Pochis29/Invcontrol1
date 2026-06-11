<?php
// ============================================================
// InvControl – Helper: Sistema de Permisos por Módulo
// Archivo: app/helpers/Permisos.php
// ============================================================

class Permisos {

    private static ?array $cache = null;

    // Niveles de acceso
    const SIN_ACCESO = 0;
    const VER        = 1;
    const EDITAR     = 2;

    // Módulos del sistema
    const MODULOS = [
        'productos'   => 'Productos',
        'movimientos' => 'Movimientos (Entradas/Salidas)',
        'despachos'   => 'Órdenes de Despacho',
        'proveedores' => 'Proveedores',
        'categorias'  => 'Categorías',
        'reportes'    => 'Reportes y Kardex',
        'scanner'     => 'Etiquetas y Scanner',
        'usuarios'    => 'Gestión de Usuarios',
    ];

    const NIVELES = [
        0 => 'Sin acceso',
        1 => 'Ver',
        2 => 'Editar',
    ];

    const NIVEL_COLORES = [
        0 => 'secondary',
        1 => 'warning',
        2 => 'success',
    ];

    // ── Cargar permisos del usuario en sesión ─────────────────
    public static function cargar(): void {
        if (self::$cache !== null) return;
        if (empty($_SESSION['usuario_id'])) { self::$cache = []; return; }

        // Administrador = acceso total siempre
        if (($_SESSION['rol'] ?? '') === 'admin') {
            self::$cache = array_fill_keys(array_keys(self::MODULOS), self::EDITAR);
            return;
        }

        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT modulo, nivel FROM usuario_permisos WHERE usuario_id = ?");
        $stmt->execute([$_SESSION['usuario_id']]);
        $rows = $stmt->fetchAll();

        self::$cache = array_fill_keys(array_keys(self::MODULOS), self::SIN_ACCESO);
        foreach ($rows as $row) {
            if (isset(self::$cache[$row['modulo']])) {
                self::$cache[$row['modulo']] = (int)$row['nivel'];
            }
        }
    }

    // ── Verificar nivel de un módulo ──────────────────────────
    public static function nivel(string $modulo): int {
        self::cargar();
        return self::$cache[$modulo] ?? self::SIN_ACCESO;
    }

    public static function puede(string $modulo, int $nivelRequerido = self::VER): bool {
        return self::nivel($modulo) >= $nivelRequerido;
    }

    public static function puedeVer(string $modulo): bool {
        return self::puede($modulo, self::VER);
    }

    public static function puedeEditar(string $modulo): bool {
        return self::puede($modulo, self::EDITAR);
    }

    // ── Verificar y redirigir si no tiene acceso ──────────────
    public static function requerir(string $modulo, int $nivel = self::VER): void {
        self::cargar();
        if (!self::puede($modulo, $nivel)) {
            $_SESSION['error'] = 'No tienes permiso para acceder a esa sección.';
            header('Location: ' . APP_URL . '/');
            exit;
        }
    }

    // ── Obtener todos los permisos de un usuario (para editar) ─
    public static function getDeUsuario(int $usuarioId): array {
        $db   = Database::getConnection();
        $stmt = $db->prepare("SELECT modulo, nivel FROM usuario_permisos WHERE usuario_id = ?");
        $stmt->execute([$usuarioId]);
        $rows = $stmt->fetchAll();

        $permisos = array_fill_keys(array_keys(self::MODULOS), self::SIN_ACCESO);
        foreach ($rows as $row) {
            if (isset($permisos[$row['modulo']])) {
                $permisos[$row['modulo']] = (int)$row['nivel'];
            }
        }
        return $permisos;
    }

    // ── Guardar permisos de un usuario ────────────────────────
    public static function guardar(int $usuarioId, array $permisos): void {
        $db   = Database::getConnection();
        $stmt = $db->prepare("
            INSERT INTO usuario_permisos (usuario_id, modulo, nivel)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE nivel = VALUES(nivel)
        ");
        foreach (self::MODULOS as $modulo => $nombre) {
            $nivel = (int)($permisos[$modulo] ?? self::SIN_ACCESO);
            $nivel = max(0, min(2, $nivel)); // clamp 0-2
            $stmt->execute([$usuarioId, $modulo, $nivel]);
        }
        // Limpiar caché si es el usuario en sesión
        if ($usuarioId === (int)($_SESSION['usuario_id'] ?? 0)) {
            self::$cache = null;
        }
    }

    // ── Permisos por defecto según rol ────────────────────────
    public static function defaultsPorRol(string $rol): array {
        if ($rol === 'admin') {
            return array_fill_keys(array_keys(self::MODULOS), self::EDITAR);
        }
        return [
            'productos'   => self::VER,
            'movimientos' => self::EDITAR,
            'despachos'   => self::EDITAR,
            'proveedores' => self::VER,
            'categorias'  => self::SIN_ACCESO,
            'reportes'    => self::SIN_ACCESO,
            'scanner'     => self::EDITAR,
            'usuarios'    => self::SIN_ACCESO,
        ];
    }
}
