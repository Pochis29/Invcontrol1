<?php
$esEdicion   = !empty($producto) && !empty($producto['id'] ?? '');
$pageTitle   = $esEdicion ? 'Editar Producto' : 'Nuevo Producto';
$page        = 'productos';
$empaques    = $empaques ?? [];
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="card" style="max-width:780px">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-box-seam text-primary"></i> <?= $pageTitle ?>
    </div>
    <div class="card-body">
        <form method="POST"
              action="<?= APP_URL ?>/?page=productos&action=<?= $esEdicion ? 'actualizar' : 'guardar' ?>">
            <?php if ($esEdicion): ?>
                <input type="hidden" name="id" value="<?= $producto['id'] ?>">
            <?php endif; ?>

            <div class="row g-3">

                <!-- Categoría -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">
                        Categoría <small class="text-primary">(selecciona primero)</small>
                    </label>
                    <select name="categoria_id" id="selectCategoria" class="form-select">
                        <option value="">-- Seleccione categoría --</option>
                        <?php foreach ($categorias as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                    data-prefijo="<?= htmlspecialchars($c['prefijo'] ?? '') ?>"
                                    <?= ($producto['categoria_id'] ?? '') == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['nombre']) ?>
                                <?php if (!empty($c['prefijo'])): ?>(<?= $c['prefijo'] ?>)<?php endif; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Código automático -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Código <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <input type="text" name="codigo" id="inputCodigo"
                               class="form-control font-monospace fw-bold"
                               style="letter-spacing:.08em" required
                               placeholder="Selecciona categoría..."
                               value="<?= htmlspecialchars($producto['codigo'] ?? '') ?>">
                        <button type="button" class="btn btn-outline-primary"
                                onclick="regenerarCodigo()" id="btnRegenerar">
                            <i class="bi bi-arrow-clockwise"></i>
                        </button>
                    </div>
                    <div class="form-text" id="codigoInfo">
                        <i class="bi bi-magic text-primary"></i> El código se genera al seleccionar la categoría.
                    </div>
                </div>

                <!-- Código de barras del producto (unidad individual) -->
                <div class="col-md-8">
                    <label class="form-label fw-semibold">
                        <i class="bi bi-upc-scan text-secondary"></i>
                        Código de Barras de la Unidad
                        <span class="badge bg-secondary fw-normal ms-1">opcional</span>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="bi bi-upc-scan"></i></span>
                        <input type="text" name="codigo_barras" id="inputCodigoBarras"
                               class="form-control font-monospace"
                               placeholder="Escanea o escribe el código del empaque individual..."
                               value="<?= htmlspecialchars($producto['codigo_barras'] ?? '') ?>">
                        <button type="button" class="btn btn-outline-secondary"
                                onclick="document.getElementById('inputCodigoBarras').value=''">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                    <div class="form-text">
                        <i class="bi bi-info-circle text-primary"></i>
                        Código EAN/UPC de la unidad individual (la más pequeña que vendes).
                    </div>
                </div>

                <!-- Unidad base -->
                <div class="col-md-4">
                    <label class="form-label fw-semibold">
                        Unidad base <span class="text-danger">*</span>
                    </label>
                    <select name="unidad_base" class="form-select">
                        <?php
                        $unidades = ['Unidad','Litro','Kilogramo','Gramo','Metro','Par','Docena','Caja','Bolsa','Rollo'];
                        $actual   = $producto['unidad_base'] ?? 'Unidad';
                        foreach ($unidades as $u):
                        ?>
                            <option value="<?= $u ?>" <?= $actual === $u ? 'selected' : '' ?>><?= $u ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text">La medida mínima de tu inventario.</div>
                </div>

                <!-- Nombre -->
                <div class="col-12">
                    <label class="form-label fw-semibold">Nombre del producto <span class="text-danger">*</span></label>
                    <input type="text" name="nombre" class="form-control" required
                           placeholder="Ej: Crema de Maní 200g"
                           value="<?= htmlspecialchars($producto['nombre'] ?? '') ?>">
                </div>

                <!-- Descripción -->
                <div class="col-12">
                    <label class="form-label fw-semibold">Descripción</label>
                    <textarea name="descripcion" class="form-control" rows="2"
                              placeholder="Detalles adicionales (opcional)..."><?= htmlspecialchars($producto['descripcion'] ?? '') ?></textarea>
                </div>

                <!-- Proveedor -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Proveedor</label>
                    <select name="proveedor_id" class="form-select">
                        <option value="">-- Seleccione --</option>
                        <?php foreach ($proveedores as $pv): ?>
                            <option value="<?= $pv['id'] ?>"
                                    <?= ($producto['proveedor_id'] ?? '') == $pv['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($pv['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <?php if (!$esEdicion): ?>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Stock Inicial</label>
                    <input type="number" name="stock_actual" class="form-control" min="0" value="0">
                </div>
                <?php endif; ?>

                <div class="col-md-<?= $esEdicion ? '6' : '3' ?>">
                    <label class="form-label fw-semibold">Stock Mínimo</label>
                    <input type="number" name="stock_minimo" class="form-control"
                           min="0" value="<?= $producto['stock_minimo'] ?? 5 ?>">
                    <div class="form-text">Alerta cuando el stock baje de este valor.</div>
                </div>

            </div><!-- /row básico -->

            <!-- ══════════════════════════════════════════════════
                 SECCIÓN DE EMPAQUES (WMS)
            ══════════════════════════════════════════════════ -->
            <hr class="my-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h6 class="mb-0 fw-semibold">
                        <i class="bi bi-layers text-primary"></i> Empaques y presentaciones
                    </h6>
                    <small class="text-muted">
                        Define los niveles de empaque: unidad → paquete → caja máster.
                        Cada nivel puede tener su propio código de barras.
                    </small>
                </div>
                <button type="button" class="btn btn-outline-primary btn-sm" onclick="agregarEmpaque()">
                    <i class="bi bi-plus-circle"></i> Agregar nivel
                </button>
            </div>

            <!-- Ejemplo visual -->
            <div class="alert alert-light border py-2 px-3 mb-3" style="font-size:.85rem">
                <i class="bi bi-lightbulb text-warning"></i>
                <strong>Ejemplo:</strong>
                Unidad (1) → Paquete (23 unidades) → Caja máster (230 unidades).
                InvControl convierte automáticamente al registrar movimientos.
            </div>

            <div id="tablaEmpaques">
                <!-- Cabecera -->
                <div class="row g-2 mb-1 d-none d-md-flex">
                    <div class="col-md-1 text-center"><small class="text-muted fw-semibold">#</small></div>
                    <div class="col-md-3"><small class="text-muted fw-semibold">Nombre del nivel</small></div>
                    <div class="col-md-3"><small class="text-muted fw-semibold">Cantidad en unidades base</small></div>
                    <div class="col-md-4"><small class="text-muted fw-semibold">Código de barras (opcional)</small></div>
                    <div class="col-md-1"></div>
                </div>

                <div id="filaEmpaques">
                    <?php if (!empty($empaques)): ?>
                        <?php foreach ($empaques as $i => $emp): ?>
                        <div class="row g-2 mb-2 fila-empaque align-items-center">
                            <div class="col-md-1 text-center">
                                <span class="badge bg-primary rounded-pill"><?= $i + 1 ?></span>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="emp_nombre[]"
                                       class="form-control form-control-sm"
                                       placeholder="Ej: Paquete"
                                       value="<?= htmlspecialchars($emp['nombre']) ?>">
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" name="emp_cantidad[]"
                                           class="form-control" min="1" step="0.01"
                                           placeholder="Ej: 23"
                                           value="<?= $emp['cantidad'] ?>">
                                    <span class="input-group-text">uds</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="emp_codigo_barras[]"
                                       class="form-control form-control-sm font-monospace"
                                       placeholder="Escanea o escribe..."
                                       value="<?= htmlspecialchars($emp['codigo_barras'] ?? '') ?>">
                            </div>
                            <div class="col-md-1 text-center">
                                <?php if ($i > 0): ?>
                                <button type="button" class="btn btn-outline-danger btn-sm"
                                        onclick="eliminarEmpaque(this)" title="Eliminar nivel">
                                    <i class="bi bi-trash"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fila inicial: unidad base (no se puede eliminar) -->
                        <div class="row g-2 mb-2 fila-empaque align-items-center">
                            <div class="col-md-1 text-center">
                                <span class="badge bg-primary rounded-pill">1</span>
                            </div>
                            <div class="col-md-3">
                                <input type="text" name="emp_nombre[]"
                                       class="form-control form-control-sm"
                                       placeholder="Ej: Unidad"
                                       value="Unidad">
                            </div>
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <input type="number" name="emp_cantidad[]"
                                           class="form-control" min="1" step="0.01"
                                           value="1" readonly>
                                    <span class="input-group-text">uds</span>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <input type="text" name="emp_codigo_barras[]"
                                       class="form-control form-control-sm font-monospace"
                                       placeholder="Código de la unidad individual...">
                            </div>
                            <div class="col-md-1"></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <!-- Fin sección empaques -->

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary px-4">
                    <i class="bi bi-check-lg"></i>
                    <?= $esEdicion ? 'Actualizar Producto' : 'Guardar Producto' ?>
                </button>
                <a href="<?= APP_URL ?>/?page=productos" class="btn btn-outline-secondary">Cancelar</a>
            </div>

        </form>
    </div>
</div>

<?php $extraJs = '<script>
const API_URL = "' . APP_URL . '/?page=categorias&action=siguienteCodigo&categoria_id=";
let cargandoCodigo = false;
let contadorFilas  = document.querySelectorAll(".fila-empaque").length;

// ── Código automático por categoría ──────────────────────────
document.getElementById("selectCategoria").addEventListener("change", function() {
    const catId = this.value;
    if (!catId) {
        document.getElementById("inputCodigo").value       = "";
        document.getElementById("inputCodigo").placeholder = "Selecciona categoría...";
        document.getElementById("codigoInfo").innerHTML    =
            "<i class=\"bi bi-magic text-primary\"></i> El código se genera al seleccionar la categoría.";
        return;
    }
    pedirCodigo(catId);
});

async function pedirCodigo(catId) {
    if (cargandoCodigo) return;
    cargandoCodigo = true;
    const input = document.getElementById("inputCodigo");
    const info  = document.getElementById("codigoInfo");
    input.value = ""; input.placeholder = "Generando...";
    info.innerHTML = "<span class=\"text-warning\">⏳ Calculando código...</span>";
    try {
        const resp = await fetch(API_URL + catId);
        const data = await resp.json();
        if (data.codigo) {
            input.value = data.codigo; input.placeholder = "";
            info.innerHTML = `<span class="text-success">✅ Código sugerido: <strong>${data.codigo}</strong></span>`;
        }
    } catch(e) {
        info.innerHTML = "<span class=\"text-danger\">❌ Error al generar código.</span>";
    }
    cargandoCodigo = false;
}

async function regenerarCodigo() {
    const catId = document.getElementById("selectCategoria").value;
    if (!catId) { alert("Primero selecciona una categoría."); return; }
    await pedirCodigo(catId);
}

// ── Gestión de empaques ──────────────────────────────────────
function agregarEmpaque() {
    contadorFilas++;
    const contenedor = document.getElementById("filaEmpaques");
    const div = document.createElement("div");
    div.className = "row g-2 mb-2 fila-empaque align-items-center";
    div.innerHTML = `
        <div class="col-md-1 text-center">
            <span class="badge bg-primary rounded-pill">${contadorFilas}</span>
        </div>
        <div class="col-md-3">
            <input type="text" name="emp_nombre[]" class="form-control form-control-sm"
                   placeholder="Ej: Caja máster">
        </div>
        <div class="col-md-3">
            <div class="input-group input-group-sm">
                <input type="number" name="emp_cantidad[]" class="form-control"
                       min="1" step="0.01" placeholder="Ej: 230">
                <span class="input-group-text">uds</span>
            </div>
        </div>
        <div class="col-md-4">
            <input type="text" name="emp_codigo_barras[]"
                   class="form-control form-control-sm font-monospace"
                   placeholder="Escanea o escribe...">
        </div>
        <div class="col-md-1 text-center">
            <button type="button" class="btn btn-outline-danger btn-sm"
                    onclick="eliminarEmpaque(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    `;
    contenedor.appendChild(div);
    actualizarNumeracion();
    // Enfocar el nombre del nuevo empaque
    div.querySelector("input[name=\"emp_nombre[]\"]").focus();
}

function eliminarEmpaque(btn) {
    btn.closest(".fila-empaque").remove();
    actualizarNumeracion();
}

function actualizarNumeracion() {
    document.querySelectorAll(".fila-empaque").forEach((fila, i) => {
        const badge = fila.querySelector(".badge");
        if (badge) badge.textContent = i + 1;
    });
    contadorFilas = document.querySelectorAll(".fila-empaque").length;
}

// ── Prellenar código de barras si viene de escaneo ───────────
window.addEventListener("DOMContentLoaded", function() {
    const cb = document.getElementById("inputCodigoBarras");
    if (cb && cb.value) {
        cb.style.borderColor = "#0d6efd";
        cb.style.boxShadow   = "0 0 0 .2rem rgba(13,110,253,.25)";
        cb.scrollIntoView({ behavior: "smooth", block: "center" });
    }
});
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
