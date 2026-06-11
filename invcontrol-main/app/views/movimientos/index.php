<?php $pageTitle = 'Movimientos'; $page='movimientos'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex gap-2 mb-3 flex-wrap">
    <a href="<?= APP_URL ?>/?page=movimientos&action=entrada" class="btn btn-success"><i class="bi bi-arrow-down-circle"></i> Nueva Entrada</a>
    <a href="<?= APP_URL ?>/?page=movimientos&action=salida" class="btn btn-danger"><i class="bi bi-arrow-up-circle"></i> Nueva Salida</a>
</div>

<div class="card">
    <div class="card-header bg-white d-flex gap-2 flex-wrap">
        <form class="d-flex gap-2 align-items-center" method="GET">
            <input type="hidden" name="page" value="movimientos">
            <input type="date" name="desde" class="form-control form-control-sm" value="<?= $_GET['desde'] ?? date('Y-m-01') ?>">
            <span>a</span>
            <input type="date" name="hasta" class="form-control form-control-sm" value="<?= $_GET['hasta'] ?? date('Y-m-d') ?>">
            <button type="submit" class="btn btn-sm btn-outline-primary">Filtrar</button>
        </form>
    </div>
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Fecha</th><th>Producto</th><th class="text-center">Tipo</th><th class="text-center">Cantidad</th><th class="text-center">Saldo</th><th>Usuario</th><th>Observación</th></tr></thead>
            <tbody>
            <?php foreach ($movimientos as $m): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($m['fecha'])) ?></td>
                    <td><?= htmlspecialchars($m['producto_nombre']) ?> <small class="text-muted"><?= $m['codigo'] ?></small></td>
                    <td class="text-center">
                        <?php if ($m['tipo']==='entrada'): ?>
                            <span class="badge bg-success">Entrada</span>
                        <?php elseif ($m['tipo']==='salida'): ?>
                            <span class="badge bg-danger">Salida</span>
                        <?php else: ?>
                            <span class="badge bg-secondary">Ajuste</span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center fw-bold"><?= $m['cantidad'] ?></td>
                    <td class="text-center"><?= $m['stock_resultante'] ?></td>
                    <td><?= htmlspecialchars($m['usuario_nombre']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($m['observacion']??'') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($movimientos)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No hay movimientos en este rango de fechas.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
