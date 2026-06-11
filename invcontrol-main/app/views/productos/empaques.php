<?php
$page      = 'productos';
$pageTitle = 'Empaques de ' . htmlspecialchars($producto['nombre']);
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="card" style="max-width:680px">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">
            <i class="bi bi-layers text-primary"></i> Empaques y presentaciones
        </span>
        <a href="<?= APP_URL ?>/?page=productos" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
    </div>
    <div class="card-body">

        <!-- Info del producto -->
        <div class="mb-4 p-3 bg-light rounded">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1 fw-bold"><?= htmlspecialchars($producto['nombre']) ?></h6>
                    <small class="text-muted font-monospace"><?= $producto['codigo'] ?></small>
                    &nbsp;|&nbsp;
                    <small class="text-muted">Unidad base: <strong><?= $producto['unidad_base'] ?? 'Unidad' ?></strong></small>
                </div>
                <span class="badge bg-<?= $producto['stock_actual'] <= $producto['stock_minimo'] ? 'warning' : 'success' ?> fs-6">
                    Stock: <?= $producto['stock_actual'] ?>
                </span>
            </div>
        </div>

        <?php if (empty($empaques)): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                Este producto no tiene empaques definidos todavía.
                <a href="<?= APP_URL ?>/?page=productos&action=editar&id=<?= $producto['id'] ?>"
                   class="alert-link">Ir a editar producto</a>
            </div>
        <?php else: ?>

        <!-- Pirámide de empaques -->
        <p class="text-muted mb-3" style="font-size:.9rem">
            <i class="bi bi-info-circle text-primary"></i>
            Así está organizado el producto, de la unidad más pequeña a la más grande.
            El stock siempre se guarda en <strong><?= $producto['unidad_base'] ?? 'unidades' ?></strong>.
        </p>

        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th style="width:40px">#</th>
                        <th>Nombre</th>
                        <th class="text-center">Equivale a</th>
                        <th>Código de barras</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($empaques as $i => $emp): ?>
                    <tr>
                        <td>
                            <span class="badge bg-primary rounded-pill"><?= $emp['orden'] ?></span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($emp['nombre']) ?></strong>
                            <?php if ($emp['es_base']): ?>
                                <span class="badge bg-secondary ms-1" style="font-size:.7rem">base</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($emp['es_base']): ?>
                                <span class="text-muted">—</span>
                            <?php else: ?>
                                <span class="fw-bold text-primary"><?= number_format($emp['cantidad'], 0) ?></span>
                                <small class="text-muted"> <?= $producto['unidad_base'] ?? 'unidades' ?></small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($emp['codigo_barras'])): ?>
                                <span class="font-monospace badge bg-light text-dark border">
                                    <i class="bi bi-upc-scan"></i> <?= htmlspecialchars($emp['codigo_barras']) ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted" style="font-size:.85rem">Sin código</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Ejemplo de conversión -->
        <?php if (count($empaques) > 1):
            $mayor = end($empaques);
            $menor = reset($empaques);
        ?>
        <div class="alert alert-light border mt-3" style="font-size:.88rem">
            <i class="bi bi-calculator text-primary"></i>
            <strong>Ejemplo:</strong>
            Si recibes 2 <em><?= htmlspecialchars($mayor['nombre']) ?></em>,
            InvControl suma automáticamente
            <strong><?= 2 * $mayor['cantidad'] ?> <?= $producto['unidad_base'] ?? 'unidades' ?></strong>
            al inventario.
        </div>
        <?php endif; ?>

        <div class="mt-3">
            <a href="<?= APP_URL ?>/?page=productos&action=editar&id=<?= $producto['id'] ?>"
               class="btn btn-outline-primary btn-sm">
                <i class="bi bi-pencil"></i> Editar empaques
            </a>
        </div>

        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
