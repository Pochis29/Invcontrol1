<?php
$esEdicion = !empty($usuario);
$pageTitle = $esEdicion ? 'Editar Usuario' : 'Nuevo Usuario';
$page      = 'usuarios';
?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row g-4">
    <!-- Formulario principal -->
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header bg-white fw-semibold">
                <i class="bi bi-person-fill text-primary"></i> <?= $pageTitle ?>
            </div>
            <div class="card-body">
                <form method="POST" id="formUsuario"
                      action="<?= APP_URL ?>/?page=usuarios&action=<?= $esEdicion ? 'actualizar' : 'guardar' ?>">
                    <?php if ($esEdicion): ?>
                        <input type="hidden" name="id" value="<?= $usuario['id'] ?>">
                    <?php endif; ?>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Nombre completo <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="nombre" class="form-control" required
                               placeholder="Ej: María García"
                               value="<?= htmlspecialchars($usuario['nombre'] ?? '') ?>">
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Correo electrónico <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="email" class="form-control" required
                               placeholder="usuario@correo.com"
                               value="<?= htmlspecialchars($usuario['email'] ?? '') ?>">
                    </div>

                    <?php if (!$esEdicion): ?>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">
                            Contraseña <span class="text-danger">*</span>
                        </label>
                        <div class="input-group">
                            <input type="password" name="password" id="inputPwd"
                                   class="form-control" required minlength="6"
                                   placeholder="Mínimo 6 caracteres">
                            <button type="button" class="btn btn-outline-secondary"
                                    onclick="togglePwd()">
                                <i class="bi bi-eye" id="eyeIcon"></i>
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="mb-4">
                        <label class="form-label fw-semibold">Rol base</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="rol"
                                       id="rolAdmin" value="admin"
                                       <?= ($usuario['rol'] ?? 'operador') === 'admin' ? 'checked' : '' ?>
                                       <?= ($usuario['id'] ?? 0) == $_SESSION['usuario_id'] ? 'disabled' : '' ?>
                                       onchange="aplicarDefaults('admin')">
                                <label class="btn btn-outline-primary w-100" for="rolAdmin">
                                    <i class="bi bi-shield-fill"></i><br>
                                    <strong>Administrador</strong><br>
                                    <small>Todos los permisos</small>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="rol"
                                       id="rolOperador" value="operador"
                                       <?= ($usuario['rol'] ?? 'operador') === 'operador' ? 'checked' : '' ?>
                                       onchange="aplicarDefaults('operador')">
                                <label class="btn btn-outline-secondary w-100" for="rolOperador">
                                    <i class="bi bi-person-fill"></i><br>
                                    <strong>Operador</strong><br>
                                    <small>Permisos personalizados</small>
                                </label>
                            </div>
                        </div>
                        <div class="form-text">
                            <i class="bi bi-info-circle text-primary"></i>
                            El rol base define los permisos predeterminados. Puedes personalizarlos a la derecha.
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg"></i>
                            <?= $esEdicion ? 'Actualizar' : 'Crear Usuario' ?>
                        </button>
                        <a href="<?= APP_URL ?>/?page=usuarios"
                           class="btn btn-outline-secondary">Cancelar</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Panel de permisos -->
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header bg-white fw-semibold d-flex justify-content-between align-items-center">
                <span><i class="bi bi-shield-check text-success"></i> Permisos por módulo</span>
                <div class="btn-group btn-group-sm">
                    <button type="button" class="btn btn-outline-success"
                            onclick="setTodos(2)">Todo Editar</button>
                    <button type="button" class="btn btn-outline-warning"
                            onclick="setTodos(1)">Todo Ver</button>
                    <button type="button" class="btn btn-outline-secondary"
                            onclick="setTodos(0)">Sin acceso</button>
                </div>
            </div>
            <div class="card-body p-0">
                <table class="table mb-0" id="tablaPermisos">
                    <thead>
                        <tr>
                            <th style="width:45%">Módulo</th>
                            <th class="text-center" style="width:18%">
                                <span class="badge bg-secondary">Sin acceso</span>
                            </th>
                            <th class="text-center" style="width:18%">
                                <span class="badge bg-warning text-dark">Ver</span>
                            </th>
                            <th class="text-center" style="width:18%">
                                <span class="badge bg-success">Editar</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach (Permisos::MODULOS as $mod => $nombre): ?>
                        <?php $nivelActual = $permisosDefault[$mod] ?? 0; ?>
                        <tr id="fila_<?= $mod ?>">
                            <td class="fw-semibold small">
                                <?php
                                $iconos = [
                                    'productos'   => 'bi-box-seam',
                                    'movimientos' => 'bi-arrow-left-right',
                                    'despachos'   => 'bi-clipboard-check',
                                    'proveedores' => 'bi-building',
                                    'categorias'  => 'bi-tags',
                                    'reportes'    => 'bi-graph-up',
                                    'scanner'     => 'bi-upc-scan',
                                    'usuarios'    => 'bi-people-fill',
                                ];
                                ?>
                                <i class="bi <?= $iconos[$mod] ?? 'bi-circle' ?> text-primary me-1"></i>
                                <?= htmlspecialchars($nombre) ?>
                            </td>
                            <!-- Sin acceso -->
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input perm-radio" type="radio"
                                           name="permisos[<?= $mod ?>]"
                                           value="0"
                                           id="<?= $mod ?>_0"
                                           <?= $nivelActual == 0 ? 'checked' : '' ?>
                                           onchange="actualizarFila('<?= $mod ?>', 0)">
                                </div>
                            </td>
                            <!-- Ver -->
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input perm-radio" type="radio"
                                           name="permisos[<?= $mod ?>]"
                                           value="1"
                                           id="<?= $mod ?>_1"
                                           <?= $nivelActual == 1 ? 'checked' : '' ?>
                                           onchange="actualizarFila('<?= $mod ?>', 1)">
                                </div>
                            </td>
                            <!-- Editar -->
                            <td class="text-center">
                                <div class="form-check d-flex justify-content-center">
                                    <input class="form-check-input perm-radio" type="radio"
                                           name="permisos[<?= $mod ?>]"
                                           value="2"
                                           id="<?= $mod ?>_2"
                                           <?= $nivelActual == 2 ? 'checked' : '' ?>
                                           onchange="actualizarFila('<?= $mod ?>', 2)">
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Leyenda -->
            <div class="card-footer bg-light">
                <div class="row text-center small">
                    <div class="col-4">
                        <span class="badge bg-secondary">Sin acceso</span>
                        <div class="text-muted mt-1">No ve el módulo</div>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-warning text-dark">Ver</span>
                        <div class="text-muted mt-1">Solo consulta</div>
                    </div>
                    <div class="col-4">
                        <span class="badge bg-success">Editar</span>
                        <div class="text-muted mt-1">Crear y modificar</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Resaltar fila según nivel seleccionado */
.fila-sin-acceso td { opacity: .5; }
.fila-ver  td:first-child { border-left: 3px solid #ffc107; }
.fila-editar td:first-child { border-left: 3px solid #198754; }
.form-check-input[type="radio"] { width: 1.2em; height: 1.2em; cursor: pointer; }
</style>

<?php $extraJs = '<script>
// Defaults por rol
const DEFAULTS = {
    admin:    { productos:2, movimientos:2, despachos:2, proveedores:2, categorias:2, reportes:2, scanner:2, usuarios:2 },
    operador: { productos:1, movimientos:2, despachos:2, proveedores:1, categorias:0, reportes:0, scanner:2, usuarios:0 },
};

function aplicarDefaults(rol) {
    const d = DEFAULTS[rol];
    Object.keys(d).forEach(mod => {
        const radio = document.getElementById(mod + "_" + d[mod]);
        if (radio) { radio.checked = true; actualizarFila(mod, d[mod]); }
    });
}

function actualizarFila(mod, nivel) {
    const fila = document.getElementById("fila_" + mod);
    fila.className = ["fila-sin-acceso","fila-ver","fila-editar"][nivel] || "";
}

function setTodos(nivel) {
    document.querySelectorAll(".perm-radio").forEach(r => {
        if (r.value == nivel) { r.checked = true; }
    });
    ' . implode("\n", array_map(fn($m) => "actualizarFila('$m', nivel);",
        array_keys(Permisos::MODULOS))) . '
    // Re-aplicar con el valor correcto
    document.querySelectorAll("tbody tr").forEach(fila => {
        const mod = fila.id.replace("fila_","");
        actualizarFila(mod, nivel);
    });
}

function togglePwd() {
    const i = document.getElementById("inputPwd");
    const e = document.getElementById("eyeIcon");
    i.type = i.type === "password" ? "text" : "password";
    e.className = i.type === "password" ? "bi bi-eye" : "bi bi-eye-slash";
}

// Aplicar colores iniciales al cargar
document.querySelectorAll(".perm-radio:checked").forEach(r => {
    const name = r.name; // permisos[modulo]
    const mod  = name.match(/\[(.+)\]/)?.[1];
    if (mod) actualizarFila(mod, parseInt(r.value));
});
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
