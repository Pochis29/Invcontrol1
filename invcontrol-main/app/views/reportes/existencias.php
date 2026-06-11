<?php $pageTitle='Reporte de Existencias'; $page='reportes'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between mb-3">
    <span class="text-muted">Generado: <?= date('d/m/Y H:i') ?></span>
    <button onclick="window.print()" class="btn btn-outline-secondary btn-sm"><i class="bi bi-printer"></i> Imprimir</button>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Código</th><th>Producto</th><th>Categoría</th><th>Proveedor</th><th class="text-center">Stock Actual</th><th class="text-center">Stock Mín.</th><th class="text-center">Estado</th></tr></thead>
            <tbody>
            <?php foreach ($productos as $p): ?>
                <tr class="<?= $p['bajo_stock']?'table-warning':'' ?>">
                    <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td><?= htmlspecialchars($p['categoria_nombre']??'–') ?></td>
                    <td><?= htmlspecialchars($p['proveedor_nombre']??'–') ?></td>
                    <td class="text-center fw-bold <?= $p['bajo_stock']?'text-danger':'text-success' ?>"><?= $p['stock_actual'] ?></td>
                    <td class="text-center"><?= $p['stock_minimo'] ?></td>
                    <td class="text-center">
                        <?= $p['bajo_stock'] ? '<span class="badge bg-warning text-dark">⚠ Bajo Stock</span>' : '<span class="badge bg-success">Normal</span>' ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
