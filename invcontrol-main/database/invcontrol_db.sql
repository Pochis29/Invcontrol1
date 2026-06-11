-- ============================================================
-- InvControl – Sistema Web de Inventarios
-- Script de Base de Datos v1.0
-- ============================================================

CREATE DATABASE IF NOT EXISTS invcontrol_db
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE invcontrol_db;

-- ── TABLA: usuarios ──────────────────────────────────────────
CREATE TABLE IF NOT EXISTS usuarios (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    nombre        VARCHAR(100)  NOT NULL,
    email         VARCHAR(150)  NOT NULL UNIQUE,
    password_hash VARCHAR(255)  NOT NULL,
    rol           ENUM('admin','operador') NOT NULL DEFAULT 'operador',
    activo        TINYINT(1)    NOT NULL DEFAULT 1,
    created_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ── TABLA: categorias ────────────────────────────────────────
CREATE TABLE IF NOT EXISTS categorias (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    nombre      VARCHAR(100) NOT NULL,
    descripcion TEXT
) ENGINE=InnoDB;

-- ── TABLA: proveedores ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS proveedores (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    nombre   VARCHAR(150) NOT NULL,
    contacto VARCHAR(100),
    telefono VARCHAR(20),
    email    VARCHAR(150)
) ENGINE=InnoDB;

-- ── TABLA: productos ─────────────────────────────────────────
CREATE TABLE IF NOT EXISTS productos (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    codigo        VARCHAR(50)  NOT NULL UNIQUE,
    nombre        VARCHAR(150) NOT NULL,
    descripcion   TEXT,
    categoria_id  INT,
    proveedor_id  INT,
    stock_actual  INT          NOT NULL DEFAULT 0,
    stock_minimo  INT          NOT NULL DEFAULT 5,
    activo        TINYINT(1)   NOT NULL DEFAULT 1,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id)  REFERENCES categorias(id)  ON DELETE SET NULL,
    FOREIGN KEY (proveedor_id)  REFERENCES proveedores(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- ── TABLA: movimientos ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS movimientos (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    producto_id      INT  NOT NULL,
    usuario_id       INT  NOT NULL,
    tipo             ENUM('entrada','salida','ajuste') NOT NULL,
    cantidad         INT  NOT NULL,
    stock_resultante INT  NOT NULL,
    observacion      TEXT,
    fecha            DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (producto_id) REFERENCES productos(id)  ON DELETE CASCADE,
    FOREIGN KEY (usuario_id)  REFERENCES usuarios(id)   ON DELETE CASCADE
) ENGINE=InnoDB;

-- ── DATOS INICIALES (Seeds) ──────────────────────────────────
-- Usuarios (contraseñas: Admin123 y Oper123)
INSERT INTO usuarios (nombre, email, password_hash, rol) VALUES
('Administrador',  'admin@invcontrol.com',    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin'),
('Operador Demo',  'operador@invcontrol.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operador');

-- Categorias
INSERT INTO categorias (nombre, descripcion) VALUES
('Electrónica',   'Dispositivos y accesorios electrónicos'),
('Papelería',     'Artículos de oficina y escritura'),
('Herramientas',  'Herramientas manuales y eléctricas'),
('Limpieza',      'Productos de aseo y limpieza');

-- Proveedores
INSERT INTO proveedores (nombre, contacto, telefono, email) VALUES
('Distribuidora Tech S.A.S',  'Carlos López',   '300-123-4567', 'ventas@disttech.com'),
('Suministros Oficina Ltda',  'María García',   '311-987-6543', 'pedidos@sumofi.com'),
('Ferretería Industrial XYZ', 'Pedro Martínez', '320-555-1234', 'info@ferrxyz.com');

-- Productos de ejemplo
INSERT INTO productos (codigo, nombre, descripcion, categoria_id, proveedor_id, stock_actual, stock_minimo) VALUES
('PROD-001', 'Mouse Óptico USB',         'Mouse óptico de 3 botones, cable 1.5m',          1, 1, 25, 5),
('PROD-002', 'Teclado Estándar',         'Teclado USB español QWERTY',                     1, 1,  8, 5),
('PROD-003', 'Resma Papel A4 500 hojas', 'Papel bond blanco 75g/m²',                       2, 2, 40, 10),
('PROD-004', 'Bolígrafos Azul x12',      'Caja de 12 bolígrafos azules punta media',       2, 2,  3, 10),
('PROD-005', 'Destornillador Juego x6',  'Set de 6 destornilladores planos y de estrella', 3, 3, 12, 3),
('PROD-006', 'Jabón Líquido Antibacterial 1L', 'Jabón para manos, fragancia fresca',       4, 2, 20, 8);

-- Movimientos de ejemplo
INSERT INTO movimientos (producto_id, usuario_id, tipo, cantidad, stock_resultante, observacion) VALUES
(1, 1, 'entrada', 30, 30, 'Compra inicial – Factura 001'),
(1, 2, 'salida',   5, 25, 'Entrega área de sistemas'),
(3, 1, 'entrada', 50, 50, 'Compra inicial – Factura 002'),
(3, 2, 'salida',  10, 40, 'Entrega área administrativa'),
(4, 2, 'salida',   7,  3, 'Entrega área de atención al cliente'),
(6, 1, 'entrada', 20, 20, 'Compra inicial');
