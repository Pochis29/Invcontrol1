<?php $pageTitle = 'Cambiar Contraseña'; $page = 'usuarios'; ?>
<?php require_once __DIR__ . '/../layouts/header.php'; ?>

<div class="card" style="max-width:480px">
    <div class="card-header bg-white fw-semibold">
        <i class="bi bi-key-fill text-warning"></i> Cambiar Contraseña
    </div>
    <div class="card-body">

        <!-- Info del usuario -->
        <div class="d-flex align-items-center gap-3 p-3 rounded mb-4"
             style="background:#f8f9fa;border-left:4px solid #1F4E79">
            <div style="width:48px;height:48px;border-radius:50%;background:#1F4E79;display:flex;align-items:center;justify-content:center;color:#fff;font-weight:700;font-size:1.2rem">
                <?= strtoupper(substr($usuario['nombre'], 0, 1)) ?>
            </div>
            <div>
                <div class="fw-semibold"><?= htmlspecialchars($usuario['nombre']) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($usuario['email']) ?></div>
            </div>
        </div>

        <form method="POST"
              action="<?= APP_URL ?>/?page=usuarios&action=cambiarPassword">
            <input type="hidden" name="id" value="<?= $usuario['id'] ?>">

            <div class="mb-3">
                <label class="form-label fw-semibold">
                    Nueva contraseña <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="password" name="new_password" id="pwd1"
                           class="form-control" required minlength="6"
                           placeholder="Mínimo 6 caracteres">
                    <button type="button" class="btn btn-outline-secondary"
                            onclick="togglePwd('pwd1','eye1')">
                        <i class="bi bi-eye" id="eye1"></i>
                    </button>
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">
                    Confirmar contraseña <span class="text-danger">*</span>
                </label>
                <div class="input-group">
                    <input type="password" name="confirmar_password" id="pwd2"
                           class="form-control" required minlength="6"
                           placeholder="Repite la contraseña">
                    <button type="button" class="btn btn-outline-secondary"
                            onclick="togglePwd('pwd2','eye2')">
                        <i class="bi bi-eye" id="eye2"></i>
                    </button>
                </div>
                <div id="matchMsg" class="form-text"></div>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-warning fw-semibold" id="btnGuardar">
                    <i class="bi bi-key-fill"></i> Cambiar Contraseña
                </button>
                <a href="<?= APP_URL ?>/?page=usuarios" class="btn btn-outline-secondary">
                    Cancelar
                </a>
            </div>
        </form>
    </div>
</div>

<?php $extraJs = '<script>
function togglePwd(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    input.type  = input.type === "password" ? "text" : "password";
    icon.className = input.type === "password" ? "bi bi-eye" : "bi bi-eye-slash";
}

// Verificar que las contraseñas coincidan en tiempo real
document.getElementById("pwd2").addEventListener("input", function() {
    const p1  = document.getElementById("pwd1").value;
    const p2  = this.value;
    const msg = document.getElementById("matchMsg");
    const btn = document.getElementById("btnGuardar");
    if (p2 === "") { msg.textContent = ""; return; }
    if (p1 === p2) {
        msg.innerHTML = "<span class=\"text-success\">✅ Las contraseñas coinciden</span>";
        btn.disabled  = false;
    } else {
        msg.innerHTML = "<span class=\"text-danger\">❌ Las contraseñas no coinciden</span>";
        btn.disabled  = true;
    }
});
</script>'; ?>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>
