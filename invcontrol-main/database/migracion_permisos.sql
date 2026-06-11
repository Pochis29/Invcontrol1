-- ============================================================
-- InvControl – Migración: Sistema de Permisos por Módulo
-- Ejecutar en phpMyAdmin → invcontrol_db → SQL
-- ============================================================

USE invcontrol_db;

-- Tabla de permisos por usuario y módulo
-- nivel: 0 = Sin acceso, 1 = Ver, 2 = Editar
CREATE TABLE IF NOT EXISTS usuario_permisos (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id  INT NOT NULL,
    modulo      VARCHAR(50) NOT NULL,
    nivel       TINYINT NOT NULL DEFAULT 0 COMMENT '0=Sin acceso, 1=Ver, 2=Editar',
    UNIQUE KEY uk_usuario_modulo (usuario_id, modulo),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Permisos completos para el Admin existente (todos en nivel 2)
INSERT IGNORE INTO usuario_permisos (usuario_id, modulo, nivel)
SELECT u.id, m.modulo, 2
FROM usuarios u
CROSS JOIN (
    SELECT 'productos'   AS modulo UNION
    SELECT 'movimientos' UNION
    SELECT 'despachos'   UNION
    SELECT 'proveedores' UNION
    SELECT 'categorias'  UNION
    SELECT 'reportes'    UNION
    SELECT 'scanner'     UNION
    SELECT 'usuarios'
) m
WHERE u.rol = 'admin';

-- Permisos básicos para el Operador existente
INSERT IGNORE INTO usuario_permisos (usuario_id, modulo, nivel)
SELECT u.id, m.modulo, m.nivel
FROM usuarios u
CROSS JOIN (
    SELECT 'productos'   AS modulo, 1 AS nivel UNION
    SELECT 'movimientos', 2 UNION
    SELECT 'despachos',   2 UNION
    SELECT 'proveedores', 1 UNION
    SELECT 'categorias',  0 UNION
    SELECT 'reportes',    0 UNION
    SELECT 'scanner',     2 UNION
    SELECT 'usuarios',    0
) m
WHERE u.rol = 'operador';
