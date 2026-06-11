<?php
$pageTitle = 'Reporte Kardex – Movimientos';
$page      = 'reportes';
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- ── Encabezado del reporte ──────────────────────────────── -->
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <div>
        <p class="text-muted mb-0 small">
            <i class="bi bi-info-circle"></i>
            Filtra por fecha, producto, categoría o tipo de movimiento.
        </p>
    </div>
    <div class="d-flex gap-2">
        <a href="<?= APP_URL ?>/?page=reportes&action=existencias"
           class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-box-seam"></i> Existencias
        </a>
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            <i class="bi bi-printer"></i> Imprimir / PDF
        </button>
    </div>
</div>

<!-- ── Panel de filtros ────────────────────────────────────── -->
<div class="card mb-4 no-print">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-funnel text-primary"></i> Filtros del reporte
    </div>
    <div class="card-body">
        <form method="GET" class="row g-3 align-items-end">
            <input type="hidden" name="page"   value="reportes">
            <input type="hidden" name="action" value="kardex">

            <!-- Fechas -->
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Desde</label>
                <input type="date" name="desde" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($desde) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Hasta</label>
                <input type="date" name="hasta" class="form-control form-control-sm"
                       value="<?= htmlspecialchars($hasta) ?>">
            </div>

            <!-- Categoría -->
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Categoría</label>
                <select name="categoria_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?= $cat['id'] ?>"
                            <?= $categoriaId == $cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Producto -->
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Producto</label>
                <select name="producto_id" class="form-select form-select-sm">
                    <option value="">Todos los productos</option>
                    <?php foreach ($productos as $prod): ?>
                        <option value="<?= $prod['id'] ?>"
                            <?= $productoId == $prod['id'] ? 'selected' : '' ?>>
                            [<?= htmlspecialchars($prod['codigo']) ?>]
                            <?= htmlspecialchars($prod['nombre']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Tipo -->
            <div class="col-md-2">
                <label class="form-label fw-semibold small">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="entrada" <?= $tipo==='entrada'?'selected':'' ?>>
                        📥 Entradas
                    </option>
                    <option value="salida" <?= $tipo==='salida'?'selected':'' ?>>
                        📤 Salidas
                    </option>
                    <option value="ajuste" <?= $tipo==='ajuste'?'selected':'' ?>>
                        🔧 Ajustes
                    </option>
                </select>
            </div>

            <!-- Botones -->
            <div class="col-md-1">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-search"></i> Filtrar
                </button>
            </div>
        </form>

        <!-- Accesos rápidos de fecha -->
        <div class="mt-2 d-flex gap-2 flex-wrap">
            <small class="text-muted align-self-center">Accesos rápidos:</small>
            <?php
            $atajos = [
                'Hoy'         => [date('Y-m-d'), date('Y-m-d')],
                'Esta semana' => [date('Y-m-d', strtotime('monday this week')), date('Y-m-d')],
                'Este mes'    => [date('Y-m-01'), date('Y-m-d')],
                'Mes anterior'=> [date('Y-m-01', strtotime('first day of last month')), date('Y-m-t', strtotime('last day of last month'))],
                'Este año'    => [date('Y-01-01'), date('Y-m-d')],
            ];
            foreach ($atajos as $label => [$d, $h]):
            ?>
                <a href="<?= APP_URL ?>/?page=reportes&action=kardex&desde=<?= $d ?>&hasta=<?= $h ?>&producto_id=<?= $productoId ?>&categoria_id=<?= $categoriaId ?>&tipo=<?= $tipo ?>"
                   class="btn btn-outline-secondary btn-sm py-0">
                    <?= $label ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- ── Encabezado imprimible ─────────────────────────────────── -->
<div class="print-only mb-3" style="display:none">
    <div style="display:flex;justify-content:space-between;align-items:center;border-bottom:2px solid #1F4E79;padding-bottom:8px;margin-bottom:12px">
        <div>
            <h4 style="color:#1F4E79;margin:0">InvControl – Reporte Kardex</h4>
            <small style="color:#666">Sistema Web de Gestión de Inventarios</small>
        </div>
        <div style="text-align:right">
            <small style="color:#666">Generado: <?= date('d/m/Y H:i') ?></small><br>
            <small style="color:#666">Período: <?= date('d/m/Y', strtotime($desde)) ?> – <?= date('d/m/Y', strtotime($hasta)) ?></small>
        </div>
    </div>
</div>

<!-- ── Tarjetas de resumen ─────────────────────────────────── -->
<div class="row g-3 mb-4">
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon bg-secondary bg-opacity-10 text-secondary"
                     style="width:52px;height:52px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.5rem">
                    <i class="bi bi-list-ul"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-secondary"><?= count($movimientos) ?></div>
                    <div class="text-muted small">Total Movimientos</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon bg-success bg-opacity-10 text-success"
                     style="width:52px;height:52px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.5rem">
                    <i class="bi bi-arrow-down-circle"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-success"><?= number_format($totalEntradas) ?></div>
                    <div class="text-muted small">Unidades Entraron</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon bg-danger bg-opacity-10 text-danger"
                     style="width:52px;height:52px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.5rem">
                    <i class="bi bi-arrow-up-circle"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-danger"><?= number_format($totalSalidas) ?></div>
                    <div class="text-muted small">Unidades Salieron</div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card stat-card p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="icon bg-primary bg-opacity-10 text-primary"
                     style="width:52px;height:52px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.5rem">
                    <i class="bi bi-calculator"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold text-primary">
                        <?= number_format($totalEntradas - $totalSalidas) ?>
                    </div>
                    <div class="text-muted small">Balance Neto</div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ── Gráficas ────────────────────────────────────────────── -->
<?php if (!empty($graficaData)): ?>
<div class="row g-3 mb-4">
    <!-- Gráfica de líneas por día -->
    <div class="col-md-8">
        <div class="card p-3">
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-graph-up text-primary"></i>
                Movimientos por día
            </h6>
            <canvas id="graficaDias" height="120"></canvas>
        </div>
    </div>
    <!-- Top 5 productos -->
    <div class="col-md-4">
        <div class="card p-3">
            <h6 class="fw-semibold mb-3">
                <i class="bi bi-trophy text-warning"></i>
                Top 5 productos con más movimientos
            </h6>
            <canvas id="graficaTop" height="180"></canvas>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ── Top productos tabla ─────────────────────────────────── -->
<?php if (!empty($topProductos)): ?>
<div class="card mb-4">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-bar-chart text-primary"></i> Resumen por producto
    </div>
    <div class="card-body p-0">
        <table class="table table-sm table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Producto</th>
                    <th class="text-center">Movimientos</th>
                    <th class="text-center text-success">Entradas</th>
                    <th class="text-center text-danger">Salidas</th>
                    <th class="text-center">Balance</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($topProductos as $i => $tp): ?>
                <tr>
                    <td><span class="badge bg-secondary"><?= $i+1 ?></span></td>
                    <td>
                        <strong><?= htmlspecialchars($tp['producto_nombre']) ?></strong>
                        <small class="text-muted ms-1"><?= htmlspecialchars($tp['producto_codigo']) ?></small>
                    </td>
                    <td class="text-center fw-bold"><?= $tp['total_movimientos'] ?></td>
                    <td class="text-center text-success fw-bold">+<?= $tp['total_entradas'] ?></td>
                    <td class="text-center text-danger fw-bold">-<?= $tp['total_salidas'] ?></td>
                    <td class="text-center fw-bold <?= ($tp['total_entradas']-$tp['total_salidas'])>=0?'text-primary':'text-danger' ?>">
                        <?= number_format($tp['total_entradas'] - $tp['total_salidas']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<!-- ── Tabla principal de Kardex ──────────────────────────── -->
<div class="card">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <span class="fw-semibold">
            <i class="bi bi-clock-history text-primary"></i>
            Detalle de movimientos
            <span class="badge bg-secondary ms-2"><?= count($movimientos) ?></span>
        </span>
        <!-- Búsqueda en tabla -->
        <input type="text" id="buscarTabla" class="form-control form-control-sm no-print"
               style="width:220px" placeholder="🔍 Buscar en resultados...">
    </div>
    <div class="card-body p-0">
        <?php if (empty($movimientos)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-inbox" style="font-size:3rem;opacity:.3"></i>
                <p class="mt-2">No hay movimientos para los filtros seleccionados.</p>
            </div>
        <?php else: ?>
        <div style="overflow-x:auto">
            <table class="table table-hover mb-0" id="tablaKardex">
                <thead>
                    <tr>
                        <th>Fecha y Hora</th>
                        <th>Producto</th>
                        <th>Categoría</th>
                        <th class="text-center">Tipo</th>
                        <th class="text-center">Cantidad</th>
                        <th class="text-center">Saldo</th>
                        <th>Registrado por</th>
                        <th>Observación</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($movimientos as $m): ?>
                    <tr>
                        <td class="small font-monospace">
                            <?= date('d/m/Y', strtotime($m['fecha'])) ?>
                            <span class="text-muted"><?= date('H:i', strtotime($m['fecha'])) ?></span>
                        </td>
                        <td>
                            <strong><?= htmlspecialchars($m['producto_nombre']) ?></strong>
                            <br><small class="text-muted"><?= htmlspecialchars($m['producto_codigo']) ?></small>
                        </td>
                        <td class="small text-muted">
                            <?= htmlspecialchars($m['categoria_nombre'] ?? '–') ?>
                        </td>
                        <td class="text-center">
                            <?php if ($m['tipo']==='entrada'): ?>
                                <span class="badge bg-success">
                                    <i class="bi bi-arrow-down"></i> Entrada
                                </span>
                            <?php elseif ($m['tipo']==='salida'): ?>
                                <span class="badge bg-danger">
                                    <i class="bi bi-arrow-up"></i> Salida
                                </span>
                            <?php else: ?>
                                <span class="badge bg-secondary">
                                    <i class="bi bi-wrench"></i> Ajuste
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center fw-bold <?= $m['tipo']==='salida'?'text-danger':'text-success' ?>">
                            <?= $m['tipo']==='salida' ? '-' : '+' ?><?= number_format($m['cantidad']) ?>
                        </td>
                        <td class="text-center fw-semibold">
                            <?= number_format($m['stock_resultante']) ?>
                        </td>
                        <td class="small"><?= htmlspecialchars($m['usuario_nombre']) ?></td>
                        <td class="small text-muted">
                            <?= htmlspecialchars($m['observacion'] ?? '–') ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
                <!-- Totales al final -->
                <tfoot>
                    <tr style="background:#f8f9fa;font-weight:bold">
                        <td colspan="4" class="text-end">TOTALES DEL PERÍODO:</td>
                        <td class="text-center">
                            <span class="text-success">+<?= number_format($totalEntradas) ?></span> /
                            <span class="text-danger">-<?= number_format($totalSalidas) ?></span>
                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- ── Estilos de impresión ────────────────────────────────── -->
<style>
@media print {
    #sidebar, #invChat-btn, #invChat-window,
    .topbar, .no-print, .btn { display: none !important; }
    #main { margin-left: 0 !important; }
    .print-only { display: block !important; }
    .card { box-shadow: none !important; border: 1px solid #ddd !important; }
    .stat-card { break-inside: avoid; }
    body { font-size: 11px; }
    .table thead th { background: #1F4E79 !important; color: white !important; -webkit-print-color-adjust: exact; }
}
</style>

<?php
// Preparar datos para gráficas
$graficaLabels   = json_encode(array_map(fn($r) => date('d/m', strtotime($r['dia'])), $graficaData));
$graficaEntradas = json_encode(array_column($graficaData, 'entradas'));
$graficaSalidas  = json_encode(array_column($graficaData, 'salidas'));
$topLabels       = json_encode(array_map(fn($r) => substr($r['producto_nombre'], 0, 15), $topProductos));
$topCounts       = json_encode(array_column($topProductos, 'total_movimientos'));
?>

<?php $extraJs = "
<script>
// ── Búsqueda en tabla ─────────────────────────────────────────
document.getElementById('buscarTabla')?.addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('#tablaKardex tbody tr').forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
    });
});

// ── Gráfica de movimientos por día ────────────────────────────
const ctxDias = document.getElementById('graficaDias');
if (ctxDias) {
    new Chart(ctxDias, {
        type: 'line',
        data: {
            labels: {$graficaLabels},
            datasets: [
                {
                    label: 'Entradas',
                    data: {$graficaEntradas},
                    borderColor: '#198754',
                    backgroundColor: 'rgba(25,135,84,.12)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#198754'
                },
                {
                    label: 'Salidas',
                    data: {$graficaSalidas},
                    borderColor: '#dc3545',
                    backgroundColor: 'rgba(220,53,69,.12)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointBackgroundColor: '#dc3545'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'top' },
                tooltip: { mode: 'index', intersect: false }
            },
            scales: {
                y: { beginAtZero: true, ticks: { stepSize: 1 } }
            }
        }
    });
}

// ── Gráfica Top 5 productos ───────────────────────────────────
const ctxTop = document.getElementById('graficaTop');
if (ctxTop) {
    new Chart(ctxTop, {
        type: 'doughnut',
        data: {
            labels: {$topLabels},
            datasets: [{
                data: {$topCounts},
                backgroundColor: [
                    '#1F4E79','#2E75B6','#5B9BD5','#9DC3E6','#BDD7EE'
                ],
                borderWidth: 2,
                borderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 11 } } }
            }
        }
    });
}
</script>
"; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
