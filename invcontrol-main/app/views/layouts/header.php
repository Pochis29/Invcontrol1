<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? APP_NAME ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root { --sidebar-w: 240px; --primary: #1F4E79; --primary-light: #2E75B6; }
        body { background: #f4f6f9; font-family: 'Segoe UI', sans-serif; }
        #sidebar { width: var(--sidebar-w); height: 100vh; background: var(--primary); position: fixed; top: 0; left: 0; z-index: 100; display: flex; flex-direction: column; overflow-y: auto; }
        #sidebar .brand { padding: 1.5rem 1rem; border-bottom: 1px solid rgba(255,255,255,.15); }
        #sidebar .brand h4 { color: #fff; margin: 0; font-weight: 700; }
        #sidebar .brand small { color: rgba(255,255,255,.6); font-size: .75rem; }
        #sidebar .nav-link { color: rgba(255,255,255,.75); padding: .65rem 1rem; border-radius: 6px; margin: 2px 8px; transition: .2s; display: flex; align-items: center; gap: .5rem; }
        #sidebar .nav-link:hover, #sidebar .nav-link.active { background: rgba(255,255,255,.15); color: #fff; }
        #sidebar .nav-section { font-size: .7rem; text-transform: uppercase; letter-spacing: .1em; color: rgba(255,255,255,.4); padding: 1rem 1rem .3rem; }
        #main { margin-left: var(--sidebar-w); padding: 0; }
        .topbar { background: #fff; border-bottom: 1px solid #e0e0e0; padding: .75rem 1.5rem; display: flex; justify-content: space-between; align-items: center; }
        .page-content { padding: 1.5rem; }
        .stat-card { border: none; border-radius: 12px; box-shadow: 0 2px 8px rgba(0,0,0,.08); }
        .stat-card .icon { width: 52px; height: 52px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem; }
        .table thead th { background: var(--primary); color: #fff; font-weight: 600; border: none; }
        .table-hover tbody tr:hover { background: #e8f0fe; }
        .badge-stock-bajo { background: #ffeaa7; color: #d35400; border: 1px solid #f39c12; }
    </style>
</head>
<body>

<nav id="sidebar">
    <div class="brand">
        <h4><i class="bi bi-boxes"></i> InvControl</h4>
        <small>Sistema de Inventarios</small>
    </div>

    <nav class="mt-2 flex-grow-1" style="overflow-y:auto">

        <!-- ══ PRINCIPAL (todos los usuarios) ══ -->
        <div class="nav-section">Principal</div>
        <a href="<?= APP_URL ?>/?page=dashboard"
           class="nav-link <?= ($page??'')==='dashboard'?'active':'' ?>">
            <i class="bi bi-speedometer2"></i> Dashboard
        </a>

        <!-- ══ INVENTARIO (todos los usuarios) ══ -->
        <div class="nav-section">Inventario</div>
        <a href="<?= APP_URL ?>/?page=productos"
           class="nav-link <?= ($page??'')==='productos'?'active':'' ?>">
            <i class="bi bi-box-seam"></i> Productos
        </a>
        <a href="<?= APP_URL ?>/?page=movimientos"
           class="nav-link <?= ($page??'')==='movimientos'?'active':'' ?>">
            <i class="bi bi-arrow-left-right"></i> Movimientos
        </a>
        <a href="<?= APP_URL ?>/?page=despachos"
           class="nav-link <?= ($page??'')==='despachos'?'active':'' ?>">
            <i class="bi bi-clipboard-check"></i> Órdenes de Despacho
        </a>
        <a href="<?= APP_URL ?>/?page=proveedores"
           class="nav-link <?= ($page??'')==='proveedores'?'active':'' ?>">
            <i class="bi bi-building"></i> Proveedores
        </a>

        <!-- ══ PISTOLERA (todos los usuarios) ══ -->
        <div class="nav-section">Pistolera</div>
        <a href="<?= APP_URL ?>/?page=scanner&action=entrada"
           class="nav-link <?= (($page??'')==='scanner'&&($_GET['action']??'')==='entrada')?'active':'' ?>">
            <i class="bi bi-arrow-down-circle"></i> Entrada Rápida
        </a>
        <a href="<?= APP_URL ?>/?page=scanner&action=salida"
           class="nav-link <?= (($page??'')==='scanner'&&($_GET['action']??'')==='salida')?'active':'' ?>">
            <i class="bi bi-arrow-up-circle"></i> Salida Rápida
        </a>

        <!-- ══ SOLO ADMINISTRADOR ══ -->
        <?php if (($_SESSION['rol']??'')==='admin'): ?>

        <div class="nav-section">Configuración</div>
        <a href="<?= APP_URL ?>/?page=categorias"
           class="nav-link <?= ($page??'')==='categorias'?'active':'' ?>">
            <i class="bi bi-tags"></i> Categorías
        </a>
        <a href="<?= APP_URL ?>/?page=scanner&action=etiquetas"
           class="nav-link <?= (($page??'')==='scanner'&&($_GET['action']??'')==='etiquetas')?'active':'' ?>">
            <i class="bi bi-upc-scan"></i> Etiquetas
        </a>

        <div class="nav-section">Reportes</div>
        <a href="<?= APP_URL ?>/?page=reportes&action=existencias" class="nav-link">
            <i class="bi bi-clipboard-data"></i> Existencias
        </a>
        <a href="<?= APP_URL ?>/?page=reportes&action=movimientos" class="nav-link">
            <i class="bi bi-graph-up"></i> Movimientos
        </a>
        <a href="<?= APP_URL ?>/?page=reportes&action=kardex"
           class="nav-link <?= (($page??'')==='reportes'&&($_GET['action']??'')==='kardex')?'active':'' ?>">
            <i class="bi bi-clock-history"></i> Kardex Completo
        </a>

        <div class="nav-section">Administración</div>
        <a href="<?= APP_URL ?>/?page=usuarios"
           class="nav-link <?= ($page??'')==='usuarios'?'active':'' ?>">
            <i class="bi bi-people-fill"></i> Usuarios
        </a>

        <?php endif; ?>

    </nav>

    <!-- Pie del sidebar -->
    <div class="p-3" style="border-top: 1px solid rgba(255,255,255,.15)">
        <small class="text-white-50 d-block mb-1">
            <i class="bi bi-person-circle"></i>
            <?= htmlspecialchars($_SESSION['nombre']??'') ?>
        </small>
        <small class="text-white-50 d-block mb-2">
            <span class="badge bg-secondary">
                <?= htmlspecialchars($_SESSION['rol']??'') ?>
            </span>
        </small>
        <a href="<?= APP_URL ?>/?page=auth&action=logout"
           class="btn btn-sm btn-outline-light w-100">
            <i class="bi bi-box-arrow-right"></i> Salir
        </a>
    </div>
</nav>

<!-- Contenido principal -->
<div id="main">
    <div class="topbar">
        <h5 class="mb-0 fw-semibold"><?= $pageTitle ?? 'Dashboard' ?></h5>
        <small class="text-muted"><?= date('d/m/Y H:i') ?></small>
    </div>
    <div class="page-content">

    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle-fill"></i>
            <?= htmlspecialchars($_SESSION['success']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
