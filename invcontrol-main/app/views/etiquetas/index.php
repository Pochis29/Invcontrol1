<?php $pageTitle = 'Generador de Etiquetas'; $page = 'scanner'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0">
        Genera e imprime etiquetas con código de barras para tus productos.
    </p>
    <div class="d-flex gap-2">
        <button onclick="seleccionarTodos()" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-check-all"></i> Seleccionar todos
        </button>
        <button onclick="imprimirSeleccionados()" class="btn btn-primary btn-sm">
            <i class="bi bi-printer"></i> Imprimir seleccionados
        </button>
    </div>
</div>

<!-- Filtro -->
<div class="card mb-3">
    <div class="card-body py-2">
        <input type="text" id="filtroEtiqueta" class="form-control form-control-sm"
               placeholder="🔍 Filtrar por nombre o código...">
    </div>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0" id="tablaEtiquetas">
            <thead>
                <tr>
                    <th style="width:40px">
                        <input type="checkbox" id="checkAll" class="form-check-input"
                               onchange="toggleAll(this.checked)">
                    </th>
                    <th>Código</th>
                    <th>Nombre</th>
                    <th>Categoría</th>
                    <th class="text-center">Stock</th>
                    <th class="text-center">Etiqueta</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($productos as $p): ?>
                <tr>
                    <td>
                        <input type="checkbox" class="form-check-input prod-check"
                               value="<?= $p['id'] ?>" data-nombre="<?= htmlspecialchars($p['nombre']) ?>">
                    </td>
                    <td><code><?= htmlspecialchars($p['codigo']) ?></code></td>
                    <td><?= htmlspecialchars($p['nombre']) ?></td>
                    <td class="text-muted small"><?= htmlspecialchars($p['categoria_nombre'] ?? '–') ?></td>
                    <td class="text-center <?= $p['bajo_stock'] ? 'text-danger fw-bold' : '' ?>">
                        <?= $p['stock_actual'] ?>
                    </td>
                    <td class="text-center">
                        <a href="<?= APP_URL ?>/?page=scanner&action=imprimir&id=<?= $p['id'] ?>"
                           target="_blank" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-upc"></i> Ver etiqueta
                        </a>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal previsualización de etiquetas múltiples -->
<div class="modal fade" id="modalEtiquetas" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-printer"></i> Vista previa – Etiquetas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="modalBody" style="background:#f4f6f9">
                <!-- Etiquetas generadas aquí -->
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<style>
@media print {
    #sidebar, #invChat-btn, .modal-header, .modal-footer, .topbar { display: none !important; }
    #main { margin-left: 0 !important; }
    .etiqueta-print { page-break-inside: avoid; }
}
</style>

<?php $extraJs = '<script>
// Filtro de tabla
document.getElementById("filtroEtiqueta").addEventListener("input", function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll("#tablaEtiquetas tbody tr").forEach(row => {
        row.style.display = row.textContent.toLowerCase().includes(q) ? "" : "none";
    });
});

function seleccionarTodos() {
    document.querySelectorAll(".prod-check").forEach(cb => cb.checked = true);
}

function toggleAll(checked) {
    document.querySelectorAll(".prod-check").forEach(cb => cb.checked = checked);
}

function imprimirSeleccionados() {
    const seleccionados = [...document.querySelectorAll(".prod-check:checked")].map(cb => ({
        id: cb.value, nombre: cb.dataset.nombre
    }));

    if (seleccionados.length === 0) {
        alert("Selecciona al menos un producto.");
        return;
    }

    const ids = seleccionados.map(s => s.id).join(",");
    window.open("' . APP_URL . '/?page=scanner&action=imprimir&ids=" + ids, "_blank");
}
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>