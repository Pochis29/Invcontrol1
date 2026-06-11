<?php
$tipo       = $_GET['action'] ?? 'entrada';
$esEntrada  = $tipo === 'entrada';
$pageTitle  = $esEntrada ? '📥 Scanner – Entrada de Mercancía' : '📤 Scanner – Salida de Mercancía';
$page       = 'scanner';
$colorClass = $esEntrada ? 'success' : 'danger';
$colorHex   = $esEntrada ? '#198754' : '#dc3545';
$iconTipo   = $esEntrada ? 'bi-arrow-down-circle-fill' : 'bi-arrow-up-circle-fill';
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<!-- Selector Entrada / Salida -->
<div class="d-flex gap-2 mb-4">
    <a href="<?= APP_URL ?>/?page=scanner&action=entrada"
       class="btn <?= $esEntrada ? 'btn-success' : 'btn-outline-success' ?> px-4">
        <i class="bi bi-arrow-down-circle-fill"></i> Entrada
    </a>
    <a href="<?= APP_URL ?>/?page=scanner&action=salida"
       class="btn <?= !$esEntrada ? 'btn-danger' : 'btn-outline-danger' ?> px-4">
        <i class="bi bi-arrow-up-circle-fill"></i> Salida
    </a>
    <?php if (($_SESSION['rol']??'')==='admin'): ?>
    <a href="<?= APP_URL ?>/?page=scanner&action=etiquetas"
       class="btn btn-outline-secondary ms-auto">
        <i class="bi bi-upc-scan"></i> Generar Etiquetas
    </a>
    <?php endif; ?>
</div>

<div class="row g-4">

    <!-- ── Panel izquierdo: Scanner ──────────────────────────── -->
    <div class="col-lg-5">
        <div class="card border-<?= $colorClass ?>" style="border-width:2px!important">
            <div class="card-header bg-<?= $colorClass ?> text-white fw-semibold">
                <i class="bi <?= $iconTipo ?>"></i>
                <?= $esEntrada ? 'Escanear Entrada' : 'Escanear Salida' ?>
            </div>
            <div class="card-body">

                <!-- Campo de escaneo -->
                <label class="form-label fw-semibold">
                    <i class="bi bi-upc-scan"></i> Código de barras / Código producto
                </label>
                <div class="input-group mb-2">
                    <input type="text" id="codigoScanner"
                           class="form-control form-control-lg"
                           placeholder="Apunta la pistolera aquí..."
                           autofocus autocomplete="off"
                           style="font-family: monospace; font-size:1.1rem; letter-spacing:.1em">
                    <button class="btn btn-outline-secondary" onclick="limpiarScanner()" title="Limpiar">
                        <i class="bi bi-x-lg"></i>
                    </button>
                </div>
                <div class="form-text mb-3">
                    <i class="bi bi-info-circle text-primary"></i>
                    La pistolera envía el código y presiona Enter automáticamente.
                    También puedes escribir el código manualmente.
                </div>

                <!-- Estado del escaneo -->
                <div id="scanStatus" class="alert alert-secondary py-2 text-center d-none"></div>

                <!-- Beep visual indicator -->
                <div id="scanFlash" class="rounded p-2 text-center fw-bold d-none"
                     style="background:#d4edda; color:#155724; font-size:.9rem">
                    ✅ Código detectado
                </div>
            </div>
        </div>

        <!-- Historial de escaneos de esta sesión -->
        <div class="card mt-3">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between">
                <span><i class="bi bi-clock-history text-secondary"></i> Historial de sesión</span>
                <button class="btn btn-sm btn-outline-secondary" onclick="limpiarHistorial()">Limpiar</button>
            </div>
            <div class="card-body p-0" style="max-height:260px; overflow-y:auto">
                <table class="table table-sm mb-0" id="tablaHistorial">
                    <thead class="table-light">
                        <tr><th>Hora</th><th>Producto</th><th>Cant.</th><th>Tipo</th></tr>
                    </thead>
                    <tbody id="historialBody">
                        <tr id="emptyHistorial">
                            <td colspan="4" class="text-center text-muted py-3">
                                Sin registros en esta sesión
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ── Panel derecho: Detalle del producto ───────────────── -->
    <div class="col-lg-7">
        <!-- Placeholder cuando no hay producto -->
        <div id="panelVacio" class="card h-100 border-dashed">
            <div class="card-body d-flex flex-column align-items-center justify-content-center text-muted py-5">
                <i class="bi bi-upc-scan" style="font-size:4rem; opacity:.3"></i>
                <p class="mt-3 mb-0 fs-5">Esperando escaneo...</p>
                <small>Apunta la pistolera a un código de barras</small>
            </div>
        </div>

        <!-- Panel del producto encontrado -->
        <div id="panelProducto" class="d-none">
            <div class="card border-<?= $colorClass ?>" style="border-width:2px!important">
                <div class="card-header bg-<?= $colorClass ?> bg-opacity-10 d-flex justify-content-between align-items-center">
                    <span class="fw-semibold">Producto encontrado</span>
                    <span id="prodCodigo" class="badge bg-secondary font-monospace"></span>
                </div>
                <div class="card-body">
                    <h4 id="prodNombre" class="fw-bold mb-1"></h4>
                    <div class="text-muted mb-3">
                        <span id="prodCategoria"></span> •
                        <span id="prodProveedor"></span>
                    </div>

                    <!-- Stock actual -->
                    <div class="row g-3 mb-4">
                        <div class="col-6">
                            <div class="p-3 rounded text-center" style="background:#e8f4fd">
                                <div class="fs-1 fw-bold text-primary" id="prodStock">–</div>
                                <small class="text-muted">Stock actual</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-3 rounded text-center" style="background:#fff3e0">
                                <div class="fs-1 fw-bold text-warning" id="prodStockMin">–</div>
                                <small class="text-muted">Stock mínimo</small>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de cantidad -->
                    <input type="hidden" id="prodId">
                    <input type="hidden" id="tipoMov" value="<?= $tipo ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold fs-5">
                            Cantidad a <?= $esEntrada ? 'ingresar' : 'retirar' ?>
                        </label>
                        <div class="input-group input-group-lg">
                            <button class="btn btn-outline-secondary" onclick="ajustarCantidad(-1)">
                                <i class="bi bi-dash-lg"></i>
                            </button>
                            <input type="number" id="cantidad" class="form-control text-center fw-bold"
                                   value="1" min="1" style="font-size:1.8rem">
                            <button class="btn btn-outline-secondary" onclick="ajustarCantidad(1)">
                                <i class="bi bi-plus-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Observación <small class="text-muted">(opcional)</small></label>
                        <input type="text" id="observacion" class="form-control"
                               placeholder="Ej: Factura #001, Pedido #456...">
                    </div>

                    <!-- Botón registrar -->
                    <button onclick="registrarMovimiento()"
                            class="btn btn-<?= $colorClass ?> btn-lg w-100 fw-bold"
                            id="btnRegistrar">
                        <i class="bi <?= $iconTipo ?>"></i>
                        Registrar <?= ucfirst($tipo) ?>
                    </button>

                    <!-- Resultado -->
                    <div id="resultadoMov" class="mt-3 d-none"></div>
                </div>
            </div>
        </div>

        <!-- Panel de error (producto no encontrado) -->
        <div id="panelError" class="d-none">
            <div class="card border-warning" style="border-width:2px!important">
                <div class="card-body text-center py-5">
                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size:3.5rem"></i>
                    <h5 class="mt-3 fw-bold" id="errorMsg">Producto no encontrado</h5>
                    <p class="text-muted">Verifica el código o agrega el producto al sistema.</p>
                    <?php if (($_SESSION['rol']??'')==='admin'): ?>
                    <a href="<?= APP_URL ?>/?page=productos&action=nuevo"
                       class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-plus-lg"></i> Agregar producto
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>

    </div><!-- /col derecha -->
</div><!-- /row -->

<style>
.border-dashed { border: 2px dashed #dee2e6 !important; }
#codigoScanner { border: 2px solid #2E75B6; }
#codigoScanner:focus { border-color: <?= $colorHex ?>; box-shadow: 0 0 0 .2rem <?= $colorHex ?>33; }
</style>

<?php $extraJs = '<script>
// ══════════════════════════════════════════════════════════════
// SCANNER – Lógica de pistolera
// ══════════════════════════════════════════════════════════════

const API   = "' . APP_URL . '/?page=scanner&action=";
let productoActual = null;

// Auto-foco en el campo de escaneo
document.getElementById("codigoScanner").focus();

// Detectar Enter de la pistolera
document.getElementById("codigoScanner").addEventListener("keypress", function(e) {
    if (e.key === "Enter") {
        const codigo = this.value.trim();
        if (codigo) buscarProducto(codigo);
    }
});

// También buscar al dejar el campo (pistoleras que no envían Enter)
document.getElementById("codigoScanner").addEventListener("change", function() {
    const codigo = this.value.trim();
    if (codigo) buscarProducto(codigo);
});

// ── Buscar producto por código ────────────────────────────────
async function buscarProducto(codigo) {
    mostrarStatus("⏳ Buscando...", "secondary");
    flashScan();

    try {
        const fd = new FormData();
        fd.append("codigo", codigo);
        const resp = await fetch(API + "buscar", { method: "POST", body: fd });
        const data = await resp.json();

        document.getElementById("codigoScanner").value = "";
        document.getElementById("codigoScanner").focus();

        if (data.ok) {
            mostrarProducto(data.producto);
            ocultarStatus();
        } else {
            mostrarError(data.msg);
            mostrarStatus("❌ No encontrado", "danger");
        }
    } catch(e) {
        mostrarStatus("❌ Error de conexión", "danger");
    }
}

// ── Mostrar producto encontrado ───────────────────────────────
function mostrarProducto(p) {
    productoActual = p;
    document.getElementById("panelVacio").classList.add("d-none");
    document.getElementById("panelError").classList.add("d-none");
    document.getElementById("panelProducto").classList.remove("d-none");
    document.getElementById("resultadoMov").classList.add("d-none");

    document.getElementById("prodId").value       = p.id;
    document.getElementById("prodCodigo").textContent = p.codigo;
    document.getElementById("prodNombre").textContent = p.nombre;
    document.getElementById("prodCategoria").textContent = p.categoria_nombre || "Sin categoría";
    document.getElementById("prodProveedor").textContent = p.proveedor_nombre || "Sin proveedor";
    document.getElementById("prodStock").textContent    = p.stock_actual;
    document.getElementById("prodStockMin").textContent = p.stock_minimo;

    // Resaltar si stock bajo
    const stockEl = document.getElementById("prodStock");
    stockEl.style.color = p.stock_actual <= p.stock_minimo ? "#dc3545" : "#0d6efd";

    // Focus en cantidad para continuar rápido
    const cantInput = document.getElementById("cantidad");
    cantInput.value = 1;
    cantInput.focus();
    cantInput.select();

    // Sonido visual de éxito
    flashScan(true);
}

// ── Mostrar error ─────────────────────────────────────────────
function mostrarError(msg) {
    productoActual = null;
    document.getElementById("panelVacio").classList.add("d-none");
    document.getElementById("panelProducto").classList.add("d-none");
    document.getElementById("panelError").classList.remove("d-none");
    document.getElementById("errorMsg").innerHTML = msg;
}

// ── Registrar movimiento ──────────────────────────────────────
async function registrarMovimiento() {
    const id       = document.getElementById("prodId").value;
    const tipo     = document.getElementById("tipoMov").value;
    const cantidad = parseInt(document.getElementById("cantidad").value);
    const obs      = document.getElementById("observacion").value;

    if (!id || cantidad <= 0) {
        alert("Ingresa una cantidad válida.");
        return;
    }

    const btn = document.getElementById("btnRegistrar");
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Registrando...`;

    const fd = new FormData();
    fd.append("producto_id",  id);
    fd.append("tipo",         tipo);
    fd.append("cantidad",     cantidad);
    fd.append("observacion",  obs);

    try {
        const resp = await fetch(API + "registrar", { method: "POST", body: fd });
        const data = await resp.json();

        const resDiv = document.getElementById("resultadoMov");
        resDiv.classList.remove("d-none");

        if (data.ok) {
            resDiv.innerHTML = `<div class="alert alert-success py-2">
                ✅ <strong>${data.msg}</strong><br>
                Nuevo stock de <em>${data.producto}</em>: <strong>${data.nuevo_stock}</strong> unidades.
            </div>`;

            // Actualizar stock mostrado
            document.getElementById("prodStock").textContent = data.nuevo_stock;
            if (productoActual) productoActual.stock_actual = data.nuevo_stock;

            // Agregar al historial
            agregarHistorial(data.producto, cantidad, tipo);

            // Limpiar para siguiente escaneo
            document.getElementById("observacion").value = "";
            document.getElementById("cantidad").value = 1;

            // Volver al scanner después de 2s
            setTimeout(() => {
                document.getElementById("panelProducto").classList.add("d-none");
                document.getElementById("panelVacio").classList.remove("d-none");
                resDiv.classList.add("d-none");
                document.getElementById("codigoScanner").focus();
            }, 2200);

        } else {
            resDiv.innerHTML = `<div class="alert alert-danger py-2">❌ ${data.msg}</div>`;
        }

    } catch(e) {
        document.getElementById("resultadoMov").innerHTML =
            `<div class="alert alert-danger py-2">❌ Error de conexión.</div>`;
        document.getElementById("resultadoMov").classList.remove("d-none");
    }

    btn.disabled = false;
    btn.innerHTML = `<i class="bi bi-check-circle-fill"></i> Registrar`;
    document.getElementById("codigoScanner").focus();
}

// ── Historial de sesión ───────────────────────────────────────
function agregarHistorial(nombre, cantidad, tipo) {
    const tbody = document.getElementById("historialBody");
    const empty = document.getElementById("emptyHistorial");
    if (empty) empty.remove();

    const now   = new Date().toLocaleTimeString("es-CO", { hour:"2-digit", minute:"2-digit", second:"2-digit" });
    const badge = tipo === "entrada"
        ? `<span class="badge bg-success">+${cantidad}</span>`
        : `<span class="badge bg-danger">-${cantidad}</span>`;

    const tr = document.createElement("tr");
    tr.innerHTML = `<td class="font-monospace small">${now}</td>
                    <td class="small">${nombre.substring(0,22)}${nombre.length>22?"…":""}</td>
                    <td>${badge}</td>
                    <td><span class="badge bg-${tipo==="entrada"?"success":"danger"} bg-opacity-25 text-${tipo==="entrada"?"success":"danger"}">${tipo}</span></td>`;
    tbody.insertBefore(tr, tbody.firstChild);
}

function limpiarHistorial() {
    const tbody = document.getElementById("historialBody");
    tbody.innerHTML = `<tr id="emptyHistorial"><td colspan="4" class="text-center text-muted py-3">Sin registros en esta sesión</td></tr>`;
}

// ── Utilidades ────────────────────────────────────────────────
function ajustarCantidad(delta) {
    const input = document.getElementById("cantidad");
    const val   = Math.max(1, parseInt(input.value || 1) + delta);
    input.value = val;
}

function limpiarScanner() {
    document.getElementById("codigoScanner").value = "";
    document.getElementById("codigoScanner").focus();
    ocultarStatus();
}

function mostrarStatus(msg, tipo) {
    const el = document.getElementById("scanStatus");
    el.className = `alert alert-${tipo} py-2 text-center`;
    el.innerHTML = msg;
    el.classList.remove("d-none");
}

function ocultarStatus() {
    document.getElementById("scanStatus").classList.add("d-none");
}

function flashScan(ok = false) {
    const el = document.getElementById("scanFlash");
    el.style.background = ok ? "#d4edda" : "#cce5ff";
    el.style.color      = ok ? "#155724" : "#004085";
    el.textContent      = ok ? "✅ Producto encontrado" : "🔍 Código detectado";
    el.classList.remove("d-none");
    setTimeout(() => el.classList.add("d-none"), 1200);
}

// Tecla Enter en cantidad = registrar
document.getElementById("cantidad").addEventListener("keypress", function(e) {
    if (e.key === "Enter") registrarMovimiento();
});
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
