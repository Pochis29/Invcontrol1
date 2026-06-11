<?php $pageTitle = 'Nueva Orden de Despacho'; $page = 'despachos'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row g-4">
    <!-- Formulario -->
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-clipboard-plus text-primary"></i> Nueva Orden de Despacho
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/?page=despachos&action=guardar"
                      id="formOrden">

                    <!-- Datos del cliente -->
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-person-circle"></i> Datos del cliente / destinatario
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                Nombre del cliente <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="cliente" class="form-control" required
                                   placeholder="Ej: Juan García / Tienda La Esquina">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Teléfono</label>
                            <input type="text" name="telefono" class="form-control"
                                   placeholder="Ej: 300-123-4567">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Dirección de entrega</label>
                            <input type="text" name="direccion" class="form-control"
                                   placeholder="Ej: Calle 10 # 5-23, Barrio Centro">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Observación</label>
                            <textarea name="observacion" class="form-control" rows="2"
                                      placeholder="Ej: Entregar en horario de la mañana, pago contra entrega..."></textarea>
                        </div>
                    </div>

                    <hr>

                    <!-- Productos -->
                    <h6 class="fw-bold text-primary mb-3">
                        <i class="bi bi-boxes"></i> Productos a despachar
                    </h6>

                    <div id="contenedorProductos">
                        <!-- Fila de producto inicial -->
                        <div class="fila-producto row g-2 align-items-end mb-2" data-idx="0">
                            <div class="col-md-7">
                                <label class="form-label fw-semibold small">Producto</label>
                                <select name="productos[]" class="form-select select-producto"
                                        onchange="actualizarStock(this)">
                                    <option value="">-- Seleccione un producto --</option>
                                    <?php foreach ($productos as $p): ?>
                                        <option value="<?= $p['id'] ?>"
                                                data-stock="<?= $p['stock_actual'] ?>"
                                                data-codigo="<?= htmlspecialchars($p['codigo']) ?>"
                                                <?= $p['stock_actual'] <= 0 ? 'disabled' : '' ?>>
                                            [<?= htmlspecialchars($p['codigo']) ?>]
                                            <?= htmlspecialchars($p['nombre']) ?>
                                            — Stock: <?= $p['stock_actual'] ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold small">Cantidad</label>
                                <input type="number" name="cantidades[]"
                                       class="form-control input-cantidad"
                                       min="1" value="1" placeholder="0">
                                <div class="form-text stock-info text-success small"></div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-outline-danger w-100"
                                        onclick="eliminarFila(this)" style="margin-top:24px">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <button type="button" class="btn btn-outline-primary btn-sm mt-2"
                            onclick="agregarProducto()">
                        <i class="bi bi-plus-lg"></i> Agregar otro producto
                    </button>

                    <div class="mt-4 d-flex gap-2">
                        <button type="submit" class="btn btn-primary px-4">
                            <i class="bi bi-clipboard-check"></i> Crear Orden de Despacho
                        </button>
                        <a href="<?= APP_URL ?>/?page=despachos" class="btn btn-outline-secondary">
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel informativo -->
    <div class="col-lg-4">
        <div class="card border-primary">
            <div class="card-header bg-primary text-white fw-semibold">
                <i class="bi bi-info-circle"></i> ¿Cómo funciona?
            </div>
            <div class="card-body small">
                <div class="d-flex gap-2 mb-3">
                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:28px;height:28px;font-weight:700">1</div>
                    <div>
                        <strong>Crea la orden</strong><br>
                        Llena los datos del cliente y agrega los productos.
                    </div>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:28px;height:28px;font-weight:700">2</div>
                    <div>
                        <strong>Queda en Pendiente</strong><br>
                        El stock aún NO se descuenta. Puedes revisarla.
                    </div>
                </div>
                <div class="d-flex gap-2 mb-3">
                    <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:28px;height:28px;font-weight:700">3</div>
                    <div>
                        <strong>Confirma el despacho</strong><br>
                        Al confirmar, el stock se descuenta y queda en el Kardex.
                    </div>
                </div>
                <div class="d-flex gap-2">
                    <div class="rounded-circle bg-secondary text-white d-flex align-items-center justify-content-center flex-shrink-0"
                         style="width:28px;height:28px;font-weight:700">4</div>
                    <div>
                        <strong>Imprime la orden</strong><br>
                        Genera la orden imprimible para entregar con la mercancía.
                    </div>
                </div>
            </div>
        </div>

        <!-- Resumen en tiempo real -->
        <div class="card mt-3">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-receipt text-success"></i> Resumen de la orden
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0" id="tablaResumen">
                    <thead><tr><th>Producto</th><th class="text-center">Cant.</th></tr></thead>
                    <tbody id="resumenBody">
                        <tr><td colspan="2" class="text-center text-muted py-2 small">Sin productos</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Template de fila de producto -->
<template id="templateFila">
    <div class="fila-producto row g-2 align-items-end mb-2">
        <div class="col-md-7">
            <select name="productos[]" class="form-select select-producto"
                    onchange="actualizarStock(this)">
                <option value="">-- Seleccione un producto --</option>
                <?php foreach ($productos as $p): ?>
                    <option value="<?= $p['id'] ?>"
                            data-stock="<?= $p['stock_actual'] ?>"
                            <?= $p['stock_actual'] <= 0 ? 'disabled' : '' ?>>
                        [<?= htmlspecialchars($p['codigo']) ?>]
                        <?= htmlspecialchars($p['nombre']) ?>
                        — Stock: <?= $p['stock_actual'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <input type="number" name="cantidades[]" class="form-control input-cantidad"
                   min="1" value="1">
            <div class="form-text stock-info text-success small"></div>
        </div>
        <div class="col-md-2">
            <button type="button" class="btn btn-outline-danger w-100"
                    onclick="eliminarFila(this)">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</template>

<?php $extraJs = '<script>
function agregarProducto() {
    const tmpl   = document.getElementById("templateFila").content.cloneNode(true);
    document.getElementById("contenedorProductos").appendChild(tmpl);
    actualizarResumen();
}

function eliminarFila(btn) {
    const filas = document.querySelectorAll(".fila-producto");
    if (filas.length <= 1) { alert("Debe haber al menos un producto."); return; }
    btn.closest(".fila-producto").remove();
    actualizarResumen();
}

function actualizarStock(select) {
    const opt    = select.options[select.selectedIndex];
    const stock  = parseInt(opt.dataset.stock ?? 0);
    const info   = select.closest(".fila-producto").querySelector(".stock-info");
    const input  = select.closest(".fila-producto").querySelector(".input-cantidad");
    if (stock >= 0 && opt.value) {
        info.textContent = "Stock disponible: " + stock;
        info.className   = "form-text small " + (stock > 0 ? "text-success" : "text-danger fw-bold");
        input.max        = stock;
    } else {
        info.textContent = "";
    }
    actualizarResumen();
}

function actualizarResumen() {
    const tbody  = document.getElementById("resumenBody");
    const filas  = document.querySelectorAll(".fila-producto");
    let html     = "";
    let hayAlgo  = false;

    filas.forEach(fila => {
        const select = fila.querySelector(".select-producto");
        const cant   = fila.querySelector(".input-cantidad").value;
        const opt    = select.options[select.selectedIndex];
        if (opt && opt.value) {
            hayAlgo = true;
            html += `<tr><td class="small">${opt.text.split("—")[0].trim()}</td>
                         <td class="text-center fw-bold">${cant}</td></tr>`;
        }
    });

    tbody.innerHTML = hayAlgo ? html :
        `<tr><td colspan="2" class="text-center text-muted py-2 small">Sin productos</td></tr>`;
}

// Actualizar resumen al cambiar cantidades
document.getElementById("contenedorProductos").addEventListener("input", e => {
    if (e.target.classList.contains("input-cantidad")) actualizarResumen();
});
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
