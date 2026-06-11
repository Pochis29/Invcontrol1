<?php $pageTitle = 'Gestión de Usuarios'; $page = 'usuarios'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="d-flex justify-content-between align-items-center mb-3">
    <p class="text-muted mb-0 small">
        <i class="bi bi-info-circle"></i>
        Gestiona usuarios, roles y permisos por módulo.
    </p>
    <?php if (Permisos::puedeEditar('usuarios')): ?>
    <a href="<?= APP_URL ?>/?page=usuarios&action=crear" class="btn btn-primary">
        <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
    </a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-body p-0">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Correo</th>
                    <th class="text-center">Rol</th>
                    <th class="text-center">Estado</th>
                    <th>Permisos</th>
                    <th class="text-center">Acciones</th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($usuarios as $u): ?>
                <tr class="<?= !$u['activo'] ? 'table-secondary text-muted' : '' ?>">
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div style="width:36px;height:36px;border-radius:50%;
                                        background:<?= $u['rol']==='admin'?'#1F4E79':'#6c757d' ?>;
                                        display:flex;align-items:center;justify-content:center;
                                        color:#fff;font-weight:700;font-size:.85rem;flex-shrink:0">
                                <?= strtoupper(substr($u['nombre'], 0, 1)) ?>
                            </div>
                            <div>
                                <div class="fw-semibold"><?= htmlspecialchars($u['nombre']) ?></div>
                                <?php if ($u['id'] == $_SESSION['usuario_id']): ?>
                                    <span class="badge bg-info" style="font-size:.6rem">Tú</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="text-muted small"><?= htmlspecialchars($u['email']) ?></td>
                    <td class="text-center">
                        <?php if ($u['rol'] === 'admin'): ?>
                            <span class="badge" style="background:#1F4E79">
                                <i class="bi bi-shield-fill"></i> Administrador
                            </span>
                        <?php else: ?>
                            <span class="badge bg-secondary">
                                <i class="bi bi-person"></i> Operador
                            </span>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <?php if ($u['activo']): ?>
                            <span class="badge bg-success">
                                <i class="bi bi-check-circle"></i> Activo
                            </span>
                        <?php else: ?>
                            <span class="badge bg-danger">
                                <i class="bi bi-x-circle"></i> Inactivo
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['rol'] === 'admin'): ?>
                            <span class="badge bg-primary">
                                <i class="bi bi-infinity"></i> Acceso completo
                            </span>
                        <?php else: ?>
                            <?php
                            $perms  = Permisos::getDeUsuario($u['id']);
                            $editar = count(array_filter($perms, fn($n) => $n >= 2));
                            $ver    = count(array_filter($perms, fn($n) => $n == 1));
                            ?>
                            <span class="badge bg-success me-1" title="Módulos con edición">
                                <i class="bi bi-pencil"></i> <?= $editar ?>
                            </span>
                            <span class="badge bg-warning text-dark" title="Módulos solo lectura">
                                <i class="bi bi-eye"></i> <?= $ver ?>
                            </span>
                            <a href="<?= APP_URL ?>/?page=usuarios&action=permisos&id=<?= $u['id'] ?>"
                               class="btn btn-xs btn-outline-primary ms-1" style="font-size:.7rem;padding:1px 6px"
                               title="Ver permisos detallados">
                                <i class="bi bi-shield-check"></i> Editar permisos
                            </a>
                        <?php endif; ?>
                    </td>
                    <td class="text-center">
                        <div class="btn-group btn-group-sm">
                            <?php if (Permisos::puedeEditar('usuarios')): ?>
                            <a href="<?= APP_URL ?>/?page=usuarios&action=editar&id=<?= $u['id'] ?>"
                               class="btn btn-outline-primary" title="Editar">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <a href="<?= APP_URL ?>/?page=usuarios&action=password&id=<?= $u['id'] ?>"
                               class="btn btn-outline-warning" title="Cambiar contraseña">
                                <i class="bi bi-key"></i>
                            </a>
                            <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                            <a href="<?= APP_URL ?>/?page=usuarios&action=toggleActivo&id=<?= $u['id'] ?>"
                               class="btn <?= $u['activo'] ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                               title="<?= $u['activo'] ? 'Desactivar' : 'Activar' ?>"
                               onclick="return confirm('¿<?= $u['activo'] ? 'Desactivar' : 'Activar' ?> este usuario?')">
                                <i class="bi bi-<?= $u['activo'] ? 'person-dash' : 'person-check' ?>"></i>
                            </a>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
