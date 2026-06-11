<?php
$esEntrada = $tipo === 'entrada';
$pageTitle = $esEntrada ? 'Nueva Entrada de Inventario' : 'Nueva Salida de Inventario';
$page      = 'movimientos';
$btnClass  = $esEntrada ? 'btn-success' : 'btn-danger';
$icon      = $esEntrada ? 'bi-arrow-down-circle' : 'bi-arrow-up-circle';
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="card" style="max-width:640px">
    <div class="card-header bg-white fw-semibold">
        <i class="bi <?= $icon ?> <?= $esEntrada ? 'text-success' : 'text-danger' ?>"></i> <?= $pageTitle ?>
    </div>
    <div class="card-body">
        <form method="POST" action="<?= APP_URL ?>/?page=movimientos&action=guardar">
            <input type="hidden" name="tipo" value="<?= $tipo ?>">

            <!-- ── 1. BUSCAR / ESCANEAR PRODUCTO ── -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    <i class="bi bi-upc-scan text-primary"></i>
                    Buscar producto <span class="text-danger">*</span>
                </label>
                <div class="input-group mb-2">
                    <span class="input-group-text bg-primary text-white">
                        <i class="bi bi-upc-scan"></i>
                    </span>
                    <input type="text" id="campoBusqueda"
                           class="form-control form-control-lg font-monospace"
                           placeholder="Escanea cualquier código del producto (unidad, paquete o caja)..."
                           autocomplete="off" autofocus>
                    <button type="button" class="btn btn-outline-primary" onclick="buscarProducto()">
                        <i class="bi bi-search"></i> Buscar
                    </button>
                </div>
                <div class="form-text">
                    <i class="bi bi-lightbulb text-warning"></i>
                    Puedes escanear el código de <strong>cualquier empaque</strong> —
                    unidad, paquete o caja máster. InvControl convierte automáticamente.
                </div>

                <!-- Resultado: encontrado -->
                <div id="resultadoBusqueda" class="d-none mt-2">
                    <div class="alert alert-success d-flex align-items-center gap-3 py-2 mb-0">
                        <i class="bi bi-check-circle-fill fs-4 flex-shrink-0"></i>
                        <div class="flex-grow-1">
                            <strong id="resNombre"></strong><br>
                            <small class="text-muted">
                                Código: <span id="resCodigo" class="font-monospace fw-bold"></span>
                                &nbsp;|&nbsp; Stock: <span id="resStock" class="fw-bold"></span>
                                <span id="resUnidadBase"></span>
                            </small>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-secondary"
                                onclick="limpiarBusqueda()" title="Cambiar producto">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Resultado: no encontrado -->
                <div id="alertaNoEncontrado" class="d-none mt-2">
                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-exclamation-triangle-fill"></i>
                        <strong>Producto no encontrado.</strong>
                        <a id="linkCrearProducto" href="#" class="alert-link ms-2">
                            <i class="bi bi-plus-circle"></i> Crear producto con este código
                        </a>
                    </div>
                </div>

                <!-- Select oculto con el id del producto -->
                <select name="producto_id" id="selectProducto" class="d-none" required>
                    <option value="">-- Seleccione --</option>
                    <?php foreach ($productos as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- ── 2. SELECTOR DE EMPAQUE (aparece tras encontrar producto) ── -->
            <div id="seccionEmpaque" class="mb-3 d-none">
                <label class="form-label fw-semibold">
                    <i class="bi bi-layers text-primary"></i>
                    ¿En qué presentación? <span class="text-danger">*</span>
                </label>
                <select id="selectEmpaque" class="form-select" onchange="actualizarConversion()">
                    <!-- Se llena dinámicamente con JS -->
                </select>
                <div class="form-text" id="infoEmpaque"></div>
            </div>

            <!-- ── 3. CANTIDAD ── -->
            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Cantidad <span class="text-danger">*</span>
                    <span id="labelUnidadCantidad" class="text-muted fw-normal"></span>
                </label>
                <input type="number" name="cantidad" id="inputCantidad"
                       class="form-control form-control-lg" min="1" step="1"
                       required placeholder="0" disabled>

                <!-- Resumen de conversión -->
                <div id="resumenConversion" class="d-none mt-2">
                    <div class="alert alert-info py-2 mb-0" style="font-size:.9rem">
                        <i class="bi bi-calculator text-primary"></i>
                        <span id="textoConversion"></span>
                    </div>
                </div>

                <div class="form-text mt-1" id="stockAdvertencia"></div>
            </div>

            <!-- ── 4. OBSERVACIÓN ── -->
            <div class="mb-3">
                <label class="form-label fw-semibold">Observación</label>
                <textarea name="observacion" class="form-control" rows="2"
                          placeholder="Ej: Factura #001, Pedido #123..."></textarea>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" id="btnRegistrar"
                        class="btn <?= $btnClass ?> text-white px-4" disabled>
                    <i class="bi <?= $icon ?>"></i> Registrar <?= ucfirst($tipo) ?>
                </button>
                <a href="<?= APP_URL ?>/?page=movimientos" class="btn btn-outline-secondary">Cancelar</a>
            </div>
        </form>
    </div>
</div>

<?php $extraJs = '<script>
const APP_URL   = "' . APP_URL . '";
const ES_SALIDA = "' . $tipo . '" === "salida";

let productoActual = null;  // Guarda el producto encontrado
let empaquesActuales = [];  // Guarda los empaques del producto

// Pistola USB → dispara Enter automáticamente
document.getElementById("campoBusqueda").addEventListener("keydown", function(e) {
    if (e.key === "Enter") { e.preventDefault(); buscarProducto(); }
});

// Al cambiar cantidad → actualizar conversión
document.getElementById("inputCantidad").addEventListener("input", actualizarConversion);

async function buscarProducto() {
    const termino = document.getElementById("campoBusqueda").value.trim();
    if (!termino) return;

    ocultarResultados();

    try {
        const resp = await fetch(APP_URL + "/?page=productos&action=buscarPorCodigo&q=" + encodeURIComponent(termino));
        const data = await resp.json();

        if (data.encontrado) {
            productoActual   = data;
            empaquesActuales = data.empaques || [];

            // Mostrar info del producto
            document.getElementById("resNombre").textContent  = data.nombre;
            document.getElementById("resCodigo").textContent  = data.codigo;
            document.getElementById("resStock").textContent   = data.stock_actual;
            document.getElementById("resUnidadBase").textContent = " " + (data.unidad_base || "unidades");
            document.getElementById("resultadoBusqueda").classList.remove("d-none");

            // Seleccionar en el select oculto
            const select = document.getElementById("selectProducto");
            for (let opt of select.options) {
                if (opt.value == data.id) { select.value = data.id; break; }
            }

            // Llenar selector de empaques
            llenarEmpaques(data);

            // Habilitar campos
            document.getElementById("inputCantidad").disabled = false;
            document.getElementById("btnRegistrar").disabled  = false;
            document.getElementById("inputCantidad").focus();

            // Advertencia stock (salidas)
            if (ES_SALIDA) {
                const adv = document.getElementById("stockAdvertencia");
                if (parseInt(data.stock_actual) <= 0) {
                    adv.innerHTML = "<span class=\"text-danger fw-bold\">⚠️ Sin stock disponible.</span>";
                } else {
                    adv.innerHTML = "<span class=\"text-success\">Stock disponible: <strong>" + data.stock_actual + " " + (data.unidad_base||"unidades") + "</strong>.</span>";
                }
            }

        } else {
            const link = document.getElementById("linkCrearProducto");
            link.href  = APP_URL + "/?page=productos&action=nuevo&codigo_barras=" + encodeURIComponent(termino);
            document.getElementById("alertaNoEncontrado").classList.remove("d-none");
            document.getElementById("inputCantidad").disabled = true;
            document.getElementById("btnRegistrar").disabled  = true;
        }
    } catch(e) {
        alert("Error al buscar el producto. Intenta de nuevo.");
    }
}

function llenarEmpaques(data) {
    const sec    = document.getElementById("seccionEmpaque");
    const select = document.getElementById("selectEmpaque");
    select.innerHTML = "";

    if (!data.empaques || data.empaques.length === 0) {
        // Sin empaques definidos → solo unidad base
        sec.classList.add("d-none");
        return;
    }

    // Si el escaneo vino de un empaque específico, preseleccionarlo
    data.empaques.forEach(function(emp) {
        const opt = document.createElement("option");
        opt.value            = emp.id;
        opt.dataset.cantidad = emp.cantidad;
        opt.dataset.nombre   = emp.nombre;
        opt.textContent      = emp.nombre + " (" + emp.cantidad + " " + (data.unidad_base||"uds") + " c/u)";
        // Preseleccionar si el escaneo fue por este empaque
        if (data.empaque_id && data.empaque_id == emp.id) opt.selected = true;
        select.appendChild(opt);
    });

    sec.classList.remove("d-none");
    actualizarConversion();
}

function actualizarConversion() {
    if (!productoActual) return;

    const select   = document.getElementById("selectEmpaque");
    const cantidad = parseFloat(document.getElementById("inputCantidad").value) || 0;
    const info     = document.getElementById("infoEmpaque");
    const resumen  = document.getElementById("resumenConversion");
    const texto    = document.getElementById("textoConversion");
    const label    = document.getElementById("labelUnidadCantidad");

    if (!select.options.length || select.classList.contains("d-none")) {
        resumen.classList.add("d-none");
        return;
    }

    const opt          = select.options[select.selectedIndex];
    const cantEmpaque  = parseFloat(opt.dataset.cantidad) || 1;
    const nombreEmp    = opt.dataset.nombre || "unidad";
    const unidadBase   = productoActual.unidad_base || "unidades";

    label.textContent = "de " + nombreEmp;

    if (cantEmpaque === 1) {
        info.innerHTML    = "<i class=\"bi bi-info-circle\"></i> Ingresando en " + unidadBase + " individuales.";
        resumen.classList.add("d-none");
    } else {
        info.innerHTML = "<i class=\"bi bi-info-circle\"></i> 1 " + nombreEmp +
                         " = <strong>" + cantEmpaque + " " + unidadBase + "</strong>";
    }

    if (cantidad > 0 && cantEmpaque > 1) {
        const totalBase = cantidad * cantEmpaque;
        texto.innerHTML = cantidad + " " + nombreEmp +
                          " × " + cantEmpaque + " " + unidadBase + "/empaque = " +
                          "<strong>" + totalBase + " " + unidadBase + " en inventario</strong>";
        resumen.classList.remove("d-none");
    } else {
        resumen.classList.add("d-none");
    }
}

function ocultarResultados() {
    document.getElementById("resultadoBusqueda").classList.add("d-none");
    document.getElementById("alertaNoEncontrado").classList.add("d-none");
    document.getElementById("seccionEmpaque").classList.add("d-none");
    document.getElementById("resumenConversion").classList.add("d-none");
    document.getElementById("stockAdvertencia").innerHTML = "";
}

function limpiarBusqueda() {
    document.getElementById("campoBusqueda").value = "";
    document.getElementById("selectProducto").value = "";
    document.getElementById("inputCantidad").value  = "";
    document.getElementById("inputCantidad").disabled = true;
    document.getElementById("btnRegistrar").disabled  = true;
    productoActual = null;
    empaquesActuales = [];
    ocultarResultados();
    document.getElementById("campoBusqueda").focus();
}
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
