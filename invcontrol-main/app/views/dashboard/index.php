<?php $pageTitle = 'Dashboard'; $page = 'dashboard'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row g-3 mb-4">
    <!-- Stat Cards -->
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"><i class="bi bi-box-seam"></i></div>
                <div>
                    <div class="fs-2 fw-bold text-primary"><?= $totalProductos ?></div>
                    <div class="text-muted small">Total Productos</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon bg-warning bg-opacity-10 text-warning"><i class="bi bi-exclamation-triangle"></i></div>
                <div>
                    <div class="fs-2 fw-bold text-warning"><?= $bajoStock ?></div>
                    <div class="text-muted small">Bajo Stock</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"><i class="bi bi-arrow-down-circle"></i></div>
                <div>
                    <div class="fs-2 fw-bold text-success"><?= $resumenHoy['entradas_hoy'] ?? 0 ?></div>
                    <div class="text-muted small">Entradas Hoy</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon bg-danger bg-opacity-10 text-danger"><i class="bi bi-arrow-up-circle"></i></div>
                <div>
                    <div class="fs-2 fw-bold text-danger"><?= $resumenHoy['salidas_hoy'] ?? 0 ?></div>
                    <div class="text-muted small">Salidas Hoy</div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($alertas)): ?>
<div class="card border-warning mb-4">
    <div class="card-header bg-warning bg-opacity-10 fw-semibold text-warning">
        <i class="bi bi-exclamation-triangle-fill"></i> Productos con Stock Bajo
    </div>
    <div class="card-body p-0">
        <table class="table table-sm mb-0">
            <thead><tr><th>Producto</th><th>Categoría</th><th>Stock Actual</th><th>Stock Mínimo</th><th>Acción</th></tr></thead>
            <tbody>
            <?php foreach ($alertas as $a): ?>
                <tr class="table-warning">
                    <td><?= htmlspecialchars($a['nombre']) ?></td>
                    <td><?= htmlspecialchars($a['categoria_nombre'] ?? '–') ?></td>
                    <td><strong class="text-danger"><?= $a['stock_actual'] ?></strong></td>
                    <td><?= $a['stock_minimo'] ?></td>
                    <td>
                        <a href="<?= APP_URL ?>/?page=movimientos&action=entrada" class="btn btn-sm btn-outline-success">
                            <i class="bi bi-plus"></i> Entrada
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<div class="row g-3">
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-lightning-charge text-warning"></i> Accesos Rápidos</h6>
            <div class="d-grid gap-2">
                <a href="<?= APP_URL ?>/?page=movimientos&action=entrada" class="btn btn-outline-success">
                    <i class="bi bi-arrow-down-circle"></i> Registrar Entrada
                </a>
                <a href="<?= APP_URL ?>/?page=movimientos&action=salida" class="btn btn-outline-danger">
                    <i class="bi bi-arrow-up-circle"></i> Registrar Salida
                </a>
                <a href="<?= APP_URL ?>/?page=productos" class="btn btn-outline-primary">
                    <i class="bi bi-box-seam"></i> Ver Productos
                </a>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card p-3">
            <h6 class="fw-semibold mb-3"><i class="bi bi-bar-chart text-primary"></i> Movimientos del Mes</h6>
            <canvas id="chartMovimientos" height="150"></canvas>
        </div>
    </div>
</div>

<?php $extraJs = '<script>
const ctx = document.getElementById("chartMovimientos");
new Chart(ctx, {
  type: "bar",
  data: {
    labels: ["Entradas", "Salidas"],
    datasets: [{ label: "Hoy", data: [' . ($resumenHoy['entradas_hoy'] ?? 0) . ',' . ($resumenHoy['salidas_hoy'] ?? 0) . '], backgroundColor: ["#198754aa","#dc3545aa"], borderRadius: 8 }]
  },
  options: { responsive: true, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } } }
});
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
