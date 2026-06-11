<?php
$pageTitle = 'Orden ' . htmlspecialchars($orden['numero']);
$page      = 'despachos';
$estadoBg  = match($orden['estado']) {
    'pendiente'  => 'warning',
    'despachado' => 'success',
    'anulado'    => 'danger',
    default      => 'secondary'
};
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="<?= APP_URL ?>/?page=despachos" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> Volver a órdenes
    </a>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/?page=despachos&action=imprimir&id=<?= $orden['id'] ?>"
           target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-printer"></i> Imprimir orden
        </a>
        <?php if ($orden['estado'] === 'pendiente'): ?>
            <a href="<?= APP_URL ?>/?page=despachos&action=despachar&id=<?= $orden['id'] ?>"
               class="btn btn-success btn-sm fw-bold"
               onclick="return confirm('¿Confirmar el despacho? El stock se descontará y no se puede revertir.')">
                <i class="bi bi-check-circle-fill"></i> Confirmar Despacho
            </a>
            <?php if (($_SESSION['rol']??'')==='admin'): ?>
            <a href="<?= APP_URL ?>/?page=despachos&action=anular&id=<?= $orden['id'] ?>"
               class="btn btn-outline-danger btn-sm"
               onclick="return confirm('¿Anular esta orden?')">
                <i class="bi bi-x-lg"></i> Anular
            </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4">
    <!-- Datos de la orden -->
    <div class="col-md-5">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-clipboard-data text-primary"></i> Datos de la Orden</span>
                <span class="badge bg-<?= $estadoBg ?> fs-6">
                    <?= ucfirst($orden['estado']) ?>
                </span>
            </div>
            <div class="card-body">
                <table class="table table-sm mb-0">
                    <tr>
                        <td class="fw-semibold text-muted">Número:</td>
                        <td><strong class="font-monospace fs-5 text-primary"><?= htmlspecialchars($orden['numero']) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Cliente:</td>
                        <td><strong><?= htmlspecialchars($orden['cliente']) ?></strong></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Teléfono:</td>
                        <td><?= htmlspecialchars($orden['telefono'] ?? '–') ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Dirección:</td>
                        <td><?= htmlspecialchars($orden['direccion'] ?? '–') ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Creada por:</td>
                        <td><?= htmlspecialchars($orden['usuario_nombre']) ?></td>
                    </tr>
                    <tr>
                        <td class="fw-semibold text-muted">Fecha creación:</td>
                        <td><?= date('d/m/Y H:i', strtotime($orden['fecha_creacion'])) ?></td>
                    </tr>
                    <?php if ($orden['fecha_despacho']): ?>
                    <tr>
                        <td class="fw-semibold text-muted">Fecha despacho:</td>
                        <td class="text-success fw-bold"><?= date('d/m/Y H:i', strtotime($orden['fecha_despacho'])) ?></td>
                    </tr>
                    <?php endif; ?>
                    <?php if ($orden['observacion']): ?>
                    <tr>
                        <td class="fw-semibold text-muted">Observación:</td>
                        <td class="fst-italic"><?= htmlspecialchars($orden['observacion']) ?></td>
                    </tr>
                    <?php endif; ?>
                </table>
            </div>
        </div>
    </div>

    <!-- Detalle de productos -->
    <div class="col-md-7">
        <div class="card h-100">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-boxes text-primary"></i>
                Productos en esta orden
                <span class="badge bg-secondary"><?= count($orden['detalle']) ?></span>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-center">Stock al crear</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($orden['detalle'] as $d): ?>
                        <tr>
                            <td><code><?= htmlspecialchars($d['producto_codigo']) ?></code></td>
                            <td><strong><?= htmlspecialchars($d['producto_nombre']) ?></strong></td>
                            <td class="small text-muted"><?= htmlspecialchars($d['categoria_nombre'] ?? '–') ?></td>
                            <td class="text-center fw-bold text-danger fs-5"><?= $d['cantidad'] ?></td>
                            <td class="text-center text-muted"><?= $d['stock_antes'] ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr class="table-light fw-bold">
                            <td colspan="3" class="text-end">Total unidades:</td>
                            <td class="text-center text-danger fs-5">
                                <?= array_sum(array_column($orden['detalle'], 'cantidad')) ?>
                            </td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>

<?php if ($orden['estado'] === 'pendiente'): ?>
<div class="alert alert-warning mt-4">
    <i class="bi bi-exclamation-triangle-fill"></i>
    <strong>Orden pendiente.</strong> El stock aún no ha sido descontado.
    Haz clic en <strong>"Confirmar Despacho"</strong> cuando la mercancía salga del almacén.
</div>
<?php elseif ($orden['estado'] === 'despachado'): ?>
<div class="alert alert-success mt-4">
    <i class="bi bi-check-circle-fill"></i>
    <strong>Orden despachada el <?= date('d/m/Y H:i', strtotime($orden['fecha_despacho'])) ?>.</strong>
    El stock fue descontado y los movimientos quedaron registrados en el Kardex.
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
