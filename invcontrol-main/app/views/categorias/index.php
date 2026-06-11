<?php $pageTitle='Categorías'; $page='categorias'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row g-4">
    <!-- ── Formulario ──────────────────────────────────────── -->
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-tags text-primary"></i>
                <span id="formTitulo">Nueva Categoría</span>
            </div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/?page=categorias&action=guardar">
                    <input type="hidden" name="id" id="cat_id" value="">

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre <span class="text-danger">*</span></label>
                        <input type="text" name="nombre" id="cat_nombre"
                               class="form-control" required placeholder="Ej: Snacks">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Prefijo del código
                            <span class="text-muted fw-normal small">(para código automático)</span>
                        </label>
                        <input type="text" name="prefijo" id="cat_prefijo"
                               class="form-control text-uppercase" maxlength="6"
                               placeholder="Ej: SNK"
                               style="letter-spacing:.15em; font-family:monospace; font-weight:700">
                        <div class="form-text">
                            <i class="bi bi-info-circle text-primary"></i>
                            Máx. 6 letras. Los productos de esta categoría tendrán códigos como
                            <strong id="previewCodigo">SNK-001</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción</label>
                        <textarea name="descripcion" id="cat_desc"
                                  class="form-control" rows="2"
                                  placeholder="Descripción opcional..."></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i> Guardar
                        </button>
                        <button type="button" class="btn btn-outline-secondary" onclick="resetForm()">
                            <i class="bi bi-x-lg"></i> Limpiar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ── Lista de categorías ─────────────────────────────── -->
    <div class="col-md-7">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-list-ul text-secondary"></i> Categorías registradas
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th class="text-center">Prefijo</th>
                            <th>Descripción</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($categorias as $c): ?>
                        <tr>
                            <td class="fw-semibold"><?= htmlspecialchars($c['nombre']) ?></td>
                            <td class="text-center">
                                <?php if (!empty($c['prefijo'])): ?>
                                    <span class="badge bg-primary font-monospace"
                                          style="letter-spacing:.1em">
                                        <?= htmlspecialchars($c['prefijo']) ?>
                                    </span>
                                <?php else: ?>
                                    <span class="text-muted small">Sin prefijo</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted small">
                                <?= htmlspecialchars($c['descripcion'] ?? '') ?>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-primary"
                                        onclick="editCat(
                                            <?= $c['id'] ?>,
                                            '<?= addslashes($c['nombre']) ?>',
                                            '<?= addslashes($c['prefijo'] ?? '') ?>',
                                            '<?= addslashes($c['descripcion'] ?? '') ?>'
                                        )" title="Editar">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?= APP_URL ?>/?page=categorias&action=eliminar&id=<?= $c['id'] ?>"
                                   class="btn btn-sm btn-outline-danger"
                                   onclick="return confirm('¿Eliminar esta categoría?')"
                                   title="Eliminar">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (empty($categorias)): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">
                                No hay categorías. ¡Crea la primera!
                            </td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Ejemplos de prefijos -->
        <div class="card mt-3">
            <div class="card-body py-2">
                <small class="text-muted fw-semibold">
                    <i class="bi bi-lightbulb text-warning"></i> Ejemplos de prefijos:
                </small>
                <div class="d-flex flex-wrap gap-2 mt-2">
                    <?php
                    $ejemplos = [
                        'Licores'   => 'LIC',
                        'Aseo'      => 'ASE',
                        'Abarrotes' => 'ABA',
                        'Snacks'    => 'SNK',
                        'Panadería' => 'PAN',
                        'Lácteos'   => 'LAC',
                        'Bebidas'   => 'BEB',
                        'Carnes'    => 'CAR',
                    ];
                    foreach ($ejemplos as $cat => $pre): ?>
                        <span class="badge bg-light text-dark border"
                              style="cursor:pointer"
                              onclick="usarEjemplo('<?= $cat ?>', '<?= $pre ?>')"
                              title="Usar este ejemplo">
                            <?= $cat ?>
                            <span class="badge bg-primary ms-1"><?= $pre ?></span>
                        </span>
                    <?php endforeach; ?>
                </div>
                <small class="text-muted d-block mt-1">
                    Clic en cualquiera para usarlo como base ↑
                </small>
            </div>
        </div>
    </div>
</div>

<?php $extraJs = '<script>
// Preview del código en tiempo real
document.getElementById("cat_prefijo").addEventListener("input", function() {
    const val = this.value.toUpperCase();
    this.value = val;
    const preview = val ? val + "-001" : "SNK-001";
    document.getElementById("previewCodigo").textContent = preview;
});

function editCat(id, nombre, prefijo, desc) {
    document.getElementById("cat_id").value      = id;
    document.getElementById("cat_nombre").value  = nombre;
    document.getElementById("cat_prefijo").value = prefijo;
    document.getElementById("cat_desc").value    = desc;
    document.getElementById("formTitulo").textContent = "Editar Categoría";
    document.getElementById("previewCodigo").textContent = prefijo ? prefijo + "-001" : "SNK-001";
    window.scrollTo({ top: 0, behavior: "smooth" });
}

function resetForm() {
    document.getElementById("cat_id").value      = "";
    document.getElementById("cat_nombre").value  = "";
    document.getElementById("cat_prefijo").value = "";
    document.getElementById("cat_desc").value    = "";
    document.getElementById("formTitulo").textContent = "Nueva Categoría";
    document.getElementById("previewCodigo").textContent = "SNK-001";
}

function usarEjemplo(nombre, prefijo) {
    document.getElementById("cat_nombre").value  = nombre;
    document.getElementById("cat_prefijo").value = prefijo;
    document.getElementById("previewCodigo").textContent = prefijo + "-001";
    document.getElementById("cat_nombre").focus();
}
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
