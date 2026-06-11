<?php $pageTitle='Proveedores'; $page='proveedores'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card">
            <div class="card-header bg-white fw-semibold">Nueva / Editar Proveedor</div>
            <div class="card-body">
                <form method="POST" action="<?= APP_URL ?>/?page=proveedores&action=guardar">
                    <input type="hidden" name="id" id="pv_id" value="">
                    <div class="mb-2"><label class="form-label">Nombre *</label><input type="text" name="nombre" id="pv_nombre" class="form-control" required></div>
                    <div class="mb-2"><label class="form-label">Contacto</label><input type="text" name="contacto" id="pv_contacto" class="form-control"></div>
                    <div class="mb-2"><label class="form-label">Teléfono</label><input type="text" name="telefono" id="pv_telefono" class="form-control"></div>
                    <div class="mb-3"><label class="form-label">Email</label><input type="email" name="email" id="pv_email" class="form-control"></div>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                    <button type="button" class="btn btn-outline-secondary" onclick="resetPv()">Limpiar</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card"><div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead><tr><th>Nombre</th><th>Contacto</th><th>Teléfono</th><th>Email</th><th class="text-center">Acc.</th></tr></thead>
                <tbody>
                <?php foreach ($proveedores as $pv): ?>
                    <tr>
                        <td><?= htmlspecialchars($pv['nombre']) ?></td>
                        <td><?= htmlspecialchars($pv['contacto']??'') ?></td>
                        <td><?= htmlspecialchars($pv['telefono']??'') ?></td>
                        <td><?= htmlspecialchars($pv['email']??'') ?></td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-primary" onclick="editPv(<?= $pv['id'] ?>, '<?= addslashes($pv['nombre']) ?>', '<?= addslashes($pv['contacto']??'') ?>', '<?= addslashes($pv['telefono']??'') ?>', '<?= addslashes($pv['email']??'') ?>')"><i class="bi bi-pencil"></i></button>
                            <a href="<?= APP_URL ?>/?page=proveedores&action=eliminar&id=<?= $pv['id'] ?>" class="btn btn-sm btn-outline-danger" onclick="return confirm('¿Eliminar?')"><i class="bi bi-trash"></i></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div></div>
    </div>
</div>

<?php $extraJs = '<script>
function editPv(id,n,c,t,e){
    document.getElementById("pv_id").value=id;
    document.getElementById("pv_nombre").value=n;
    document.getElementById("pv_contacto").value=c;
    document.getElementById("pv_telefono").value=t;
    document.getElementById("pv_email").value=e;
}
function resetPv(){["pv_id","pv_nombre","pv_contacto","pv_telefono","pv_email"].forEach(i=>document.getElementById(i).value="");}
</script>'; ?>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
