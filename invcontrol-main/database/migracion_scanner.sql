-- ============================================================
-- InvControl – Migración: Agregar código de barras a productos
-- Ejecutar en phpMyAdmin o consola MySQL
-- ============================================================

USE invcontrol_db;

-- Agregar columna codigo_barras (opcional, para código del proveedor)
ALTER TABLE productos
    ADD COLUMN codigo_barras VARCHAR(100) DEFAULT NULL COMMENT 'Código de barras del proveedor (opcional)'
    AFTER codigo;

-- Índice para búsqueda rápida por pistolera
CREATE INDEX idx_codigo_barras ON productos(codigo_barras);

-- Ejemplo: asignar código de barras a un producto existente
-- UPDATE productos SET codigo_barras = '7701234567890' WHERE codigo = 'PROD-001';
