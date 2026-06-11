<?php $pageTitle = 'Permisos – ' . htmlspecialchars($usuario['nombre']); $page = 'usuarios'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="card" style="max-width:700px">
    <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
        <span>
            <i class="bi bi-shield-check text-success"></i>
            Permisos de <strong><?= htmlspecialchars($usuario['nombre']) ?></strong>
        </span>
        <span class="badge bg-secondary"><?= $usuario['rol'] ?></span>
    </div>
    <div class="card-body">
        <?php if ($usuario['rol'] === 'admin'): ?>
            <div class="alert alert-primary">
                <i class="bi bi-infinity"></i>
                <strong>Administrador</strong> — Este usuario tiene acceso completo a todos los módulos
                por su rol. No es necesario configurar permisos individuales.
            </div>
        <?php else: ?>
        <form method="POST" action="<?= APP_URL ?>/?page=usuarios&action=guardarPermisos">
            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

            <div class="d-flex justify-content-end gap-2 mb-3">
                <button type="button" class="btn btn-outline-success btn-sm"
                        onclick="setTodos(2)">
                    <i class="bi bi-check-all"></i> Todo Editar
                </button>
                <button type="button" class="btn btn-outline-warning btn-sm"
                        onclick="setTodos(1)">
                    <i class="bi bi-eye"></i> Todo Ver
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm"
                        onclick="setTodos(0)">
                    <i class="bi bi-x-lg"></i> Sin acceso
                </button>
            </div>

            <table class="table mb-4" id="tablaPermisos">
                <thead>
                    <tr>
                        <th>Módulo</th>
                        <th class="text-center">
                            <span class="badge bg-secondary">Sin acceso</span>
                        </th>
                        <th class="text-center">
                            <span class="badge bg-warning text-dark">Ver</span>
                        </th>
                        <th class="text-center">
                            <span class="badge bg-success">Editar</span>
                        </th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $iconos = [
                    'productos'   => ['bi-box-seam',        'Productos'],
                    'movimientos' => ['bi-arrow-left-right', 'Movimientos (Entradas/Salidas)'],
                    'despachos'   => ['bi-clipboard-check',  'Órdenes de Despacho'],
                    'proveedores' => ['bi-building',         'Proveedores'],
                    'categorias'  => ['bi-tags',             'Categorías'],
                    'reportes'    => ['bi-graph-up',         'Reportes y Kardex'],
                    'scanner'     => ['bi-upc-scan',         'Etiquetas y Scanner'],
                    'usuarios'    => ['bi-people-fill',      'Gestión de Usuarios'],
                ];
                foreach (Permisos::MODULOS as $mod => $nombre):
                    $nivel = $permisosActuales[$mod] ?? 0;
                    $icono = $iconos[$mod][0] ?? 'bi-circle';
                ?>
                    <tr id="fila_<?= $mod ?>">
                        <td class="fw-semibold">
                            <i class="bi <?= $icono ?> text-primary me-2"></i>
                            <?= htmlspecialchars($nombre) ?>
                        </td>
                        <td class="text-center">
                            <input class="form-check-input perm-radio" type="radio"
                                   name="permisos[<?= $mod ?>]" value="0"
                                   <?= $nivel == 0 ? 'checked' : '' ?>
                                   onchange="actualizarFila('<?= $mod ?>', 0)">
                        </td>
                        <td class="text-center">
                            <input class="form-check-input perm-radio" type="radio"
                                   name="permisos[<?= $mod ?>]" value="1"
                                   <?= $nivel == 1 ? 'checked' : '' ?>
                                   onchange="actualizarFila('<?= $mod ?>', 1)">
                        </td>
                        <td class="text-center">
                            <input class="form-check-input perm-radio" type="radio"
                                   name="permisos[<?= $mod ?>]" value="2"
                                   <?= $nivel == 2 ? 'checked' : '' ?>
                                   onchange="actualizarFila('<?= $mod ?>', 2)">
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-success fw-semibold">
                    <i class="bi bi-shield-check"></i> Guardar Permisos
                </button>
                <a href="<?= APP_URL ?>/?page=usuarios" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<style>
.fila-sin-acceso td { opacity:.45; }
.fila-ver  td:first-child { border-left:4px solid #ffc107; }
.fila-editar td:first-child { border-left:4px solid #198754; }
.form-check-input[type="radio"] { width:1.3em;height:1.3em;cursor:pointer; }
</style>

<?php $extraJs = '<script>
function actualizarFila(mod, nivel) {
    const fila = document.getElementById("fila_" + mod);
    if (!fila) return;
    fila.className = ["fila-sin-acceso","fila-ver","fila-editar"][nivel] || "";
}
function setTodos(nivel) {
    document.querySelectorAll("tbody tr").forEach(fila => {
        const mod = fila.id.replace("fila_","");
        const r   = document.querySelector(`input[name="permisos[${mod}]"][value="${nivel}"]`);
        if (r) { r.checked = true; actualizarFila(mod, nivel); }
    });
}
// Colorear filas al cargar
document.querySelectorAll(".perm-radio:checked").forEach(r => {
    const mod = r.name.match(/\[(.+)\]/)?.[1];
    if (mod) actualizarFila(mod, parseInt(r.value));
});
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
