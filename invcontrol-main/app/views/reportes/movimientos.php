<?php $pageTitle='Reporte de Movimientos'; $page='reportes'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="card mb-3">
    <div class="card-body">
        <form class="row g-2 align-items-end" method="GET">
            <input type="hidden" name="page" value="reportes">
            <input type="hidden" name="action" value="movimientos">
            <div class="col-auto"><label class="form-label">Desde</label><input type="date" name="desde" class="form-control" value="<?= htmlspecialchars($desde) ?>"></div>
            <div class="col-auto"><label class="form-label">Hasta</label><input type="date" name="hasta" class="form-control" value="<?= htmlspecialchars($hasta) ?>"></div>
            <div class="col-auto"><button type="submit" class="btn btn-primary">Filtrar</button></div>
            <div class="col-auto ms-auto"><button onclick="window.print()" type="button" class="btn btn-outline-secondary"><i class="bi bi-printer"></i> Imprimir</button></div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead><tr><th>Fecha</th><th>Producto</th><th class="text-center">Tipo</th><th class="text-center">Cantidad</th><th class="text-center">Saldo</th><th>Usuario</th><th>Observación</th></tr></thead>
            <tbody>
            <?php foreach ($movimientos as $m): ?>
                <tr>
                    <td><?= date('d/m/Y H:i', strtotime($m['fecha'])) ?></td>
                    <td><?= htmlspecialchars($m['producto_nombre']) ?></td>
                    <td class="text-center">
                        <?php if ($m['tipo']==='entrada'): ?><span class="badge bg-success">Entrada</span>
                        <?php elseif ($m['tipo']==='salida'): ?><span class="badge bg-danger">Salida</span>
                        <?php else: ?><span class="badge bg-secondary">Ajuste</span><?php endif; ?>
                    </td>
                    <td class="text-center fw-bold"><?= $m['cantidad'] ?></td>
                    <td class="text-center"><?= $m['stock_resultante'] ?></td>
                    <td><?= htmlspecialchars($m['usuario_nombre']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($m['observacion']??'') ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($movimientos)): ?>
                <tr><td colspan="7" class="text-center text-muted py-4">No hay movimientos en este período.</td></tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
