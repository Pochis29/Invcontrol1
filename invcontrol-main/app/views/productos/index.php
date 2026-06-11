<?php $pageTitle = 'Productos'; $page = 'productos'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <input type="text" id="buscar" class="form-control" placeholder="🔍 Buscar producto..." style="width:260px">
    </div>
    <?php if (($_SESSION['rol']??'')==='admin'): ?>
    <a href="<?= APP_URL ?>/?page=productos&action=nuevo" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nuevo Producto
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="tablaProductos">
            <thead>
                <tr>
                    <th>Código</th><th>Nombre</th><th>Categoría</th><th>Proveedor</th>
                    <th class="text-center">Stock Actual</th><th class="text-center">Stock Mín.</th>
                    <th class="text-center">Estado</th><th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['categoria_nombre'] ?? '–') ?></td>
                    <td><?= htmlspecialchars($p['proveedor_nombre'] ?? '–') ?></td>
                    <td class="text-center fw-bold <?= $p['bajo_stock'] ? 'text-danger' : 'text-success' ?>">
                        <?= $p['stock_actual'] ?>
                    </td>
                    <td class="text-center text-muted"><?= $p['stock_minimo'] ?></td>
                    <td class="text-center">
                        <?php if ($p['bajo_stock']): ?>
                            <span class="badge badge-stock-bajo"><i class="bi bi-exclamation-triangle"></i> Bajo Stock</span>
                        <?php else: ?>
                            <span class="badge bg-success-subtle text-success">Normal</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <a href="<?= APP_URL ?>/?page=productos&action=kardex&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-secondary" title="Ver Kardex">
                            <i class="bi bi-clock-history"></i>
                        </a>
                        <?php if (($_SESSION['rol']??'')==='admin'): ?>
                        <a href="<?= APP_URL ?>/?page=productos&action=editar&id=<?= $p['id'] ?>" class="btn btn-sm btn-outline-primary" title="Editar">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <a href="<?= APP_URL ?>/?page=productos&action=eliminar&id=<?= $p['id'] ?>"
                           class="btn btn-sm btn-outline-danger" title="Eliminar"
                           onclick="return confirm('¿Eliminar este producto?')">
                            <i class="bi bi-trash"></i>
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($productos)): ?>
                <tr><td colspan="8" class="text-center text-muted py-4">No hay productos registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $extraJs = '<script>
document.getElementById("buscar").addEventListener("input", function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll("#tablaProductos tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? "" : "none";
    });
});
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
