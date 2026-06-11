<?php $pageTitle = 'Kardex – ' . htmlspecialchars($producto['nombre']??''); $page='productos'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between mb-3">
    <div>
        <span class="badge bg-primary fs-6"><?= htmlspecialchars($producto['codigo']??'') ?></span>
        <span class="ms-2 fw-semibold"><?= htmlspecialchars($producto['nombre']??'') ?></span>
        <span class="ms-3 text-muted">Stock actual: <strong class="text-primary"><?= $producto['stock_actual']??0 ?></strong></span>
    </div>
    <a href="<?= APP_URL ?>/?page=productos" class="btn btn-sm btn-outline-secondary"><i class="bi bi-arrow-left"></i> Volver</a>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Fecha</th><th class="text-center">Tipo</th><th class="text-center">Cantidad</th><th class="text-center">Saldo</th><th>Registrado por</th><th>Observación</th></tr></thead>
            <tbody>
            <?php foreach ($movimientos as $m): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($m['fecha'])) ?></td>
                    <td class="text-center">
                        <?php if ($m['tipo']==='entrada'): ?>
                            <span class="badge bg-success"><i class="bi bi-arrow-down"></i> Entrada</span>
                        <?php elseif ($m['tipo']==='salida'): ?>
                            <span class="badge bg-danger"><i class="bi bi-arrow-up"></i> Salida</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Ajuste</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center fw-bold <?= $m['tipo']==='salida'?'text-danger':'text-success' ?>">
                        <?= $m['tipo']==='salida'?'-':'+' ?><?= $m['cantidad'] ?>
                    </td>
                    <td class="text-center"><?= $m['stock_resultante'] ?></td>
                    <td><?= htmlspecialchars($m['usuario_nombre']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($m['observacion']??'') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($movimientos)): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Sin movimientos registrados.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
