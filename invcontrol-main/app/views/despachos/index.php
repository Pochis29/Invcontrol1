<?php $pageTitle = 'Órdenes de Despacho'; $page = 'despachos'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0 small">
        <i class="bi bi-info-circle"></i>
        Crea una orden, agrégale productos y confírmala para descontar el stock.
    </p>
    <a href="<?= APP_URL ?>/?page=despachos&action=nueva" class="btn btn-primary">
        <i class="bi bi-plus-lg"></i> Nueva Orden de Despacho
    </a>
</div>

<!-- Filtro rápido -->
<div class="card mb-3">
    <div class="card-body py-2">
        <input type="text" id="filtro" class="form-control form-control-sm"
               placeholder="🔍 Buscar por número, cliente o estado...">
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="tablaOrdenes">
            <thead>
                <tr>
                    <th>Número</th>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th class="text-center">Productos</th>
                    <th class="text-center">Estado</th>
                    <th>Creada por</th>
                    <th>Fecha</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($ordenes as $o): ?>
                <?php
                $estadoBg = match($o['estado']) {
                    'pendiente'  => 'warning',
                    'despachado' => 'success',
                    'anulado'    => 'danger',
                    default      => 'secondary'
                };
                $estadoIcon = match($o['estado']) {
                    'pendiente'  => 'bi-clock',
                    'despachado' => 'bi-check-circle-fill',
                    'anulado'    => 'bi-x-circle-fill',
                    default      => 'bi-circle'
                };
                ?>
                <tr class="<?= $o['estado']==='anulado' ? 'table-secondary text-muted' : '' ?>">
                    <td>
                        <strong class="font-monospace text-primary">
                            <?= htmlspecialchars($o['numero']) ?>
                        </strong>
                    </td>
                    <td>
                        <strong><?= htmlspecialchars($o['cliente']) ?></strong>
                        <?php if ($o['direccion']): ?>
                            <br><small class="text-muted">
                                <i class="bi bi-geo-alt"></i>
                                <?= htmlspecialchars($o['direccion']) ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td class="small text-muted">
                        <?= htmlspecialchars($o['telefono'] ?? '–') ?>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-secondary">
                            <?= $o['total_productos'] ?> ítem(s)
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge bg-<?= $estadoBg ?>">
                            <i class="bi <?= $estadoIcon ?>"></i>
                            <?= ucfirst($o['estado']) ?>
                        </span>
                    </td>
                    <td class="small"><?= htmlspecialchars($o['usuario_nombre']) ?></td>
                    <td class="small text-muted">
                        <?= date('d/m/Y H:i', strtotime($o['fecha_creacion'])) ?>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <a href="<?= APP_URL ?>/?page=despachos&action=ver&id=<?= $o['id'] ?>"
                               class="btn btn-outline-primary" title="Ver detalle">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="<?= APP_URL ?>/?page=despachos&action=imprimir&id=<?= $o['id'] ?>"
                               target="_blank" class="btn btn-outline-secondary" title="Imprimir">
                                <i class="bi bi-printer"></i>
                            </a>
                            <?php if ($o['estado']==='pendiente' && ($_SESSION['rol']??'')==='admin'): ?>
                            <a href="<?= APP_URL ?>/?page=despachos&action=anular&id=<?= $o['id'] ?>"
                               class="btn btn-outline-danger" title="Anular"
                               onclick="return confirm('¿Anular la orden <?= $o['numero'] ?>?')">
                                <i class="bi bi-x-lg"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($ordenes)): ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-5">
                        <i class="bi bi-inbox" style="font-size:2.5rem;opacity:.3"></i>
                        <p class="mt-2 mb-0">No hay órdenes de despacho registradas.</p>
                    </td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php $extraJs = '<script>
document.getElementById("filtro").addEventListener("input", function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll("#tablaOrdenes tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? "" : "none";
    });
});
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
