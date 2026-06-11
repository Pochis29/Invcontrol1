<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Orden de Despacho <?= htmlspecialchars($orden['numero']) ?> – InvControl</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; font-size: 12px; color: #111; background: #fff; padding: 20px; }

        /* Encabezado */
        .header { display: flex; justify-content: space-between; align-items: flex-start;
                  border-bottom: 3px solid #1F4E79; padding-bottom: 12px; margin-bottom: 16px; }
        .header .marca h2 { color: #1F4E79; font-size: 22px; font-weight: 800; margin: 0; }
        .header .marca p  { color: #888; font-size: 11px; margin: 2px 0 0; }
        .header .orden-num { text-align: right; }
        .header .orden-num .num { font-size: 26px; font-weight: 900; color: #1F4E79; letter-spacing: .05em; }
        .header .orden-num .estado { display: inline-block; padding: 3px 12px; border-radius: 20px;
                                      font-weight: 700; font-size: 11px; margin-top: 4px; }
        .estado-pendiente  { background: #fff3cd; color: #856404; border: 1px solid #ffc107; }
        .estado-despachado { background: #d1e7dd; color: #0a3622; border: 1px solid #198754; }
        .estado-anulado    { background: #f8d7da; color: #58151c; border: 1px solid #dc3545; }

        /* Info de la orden */
        .info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 16px; }
        .info-box { border: 1px solid #ddd; border-radius: 6px; padding: 10px; }
        .info-box h4 { font-size: 10px; text-transform: uppercase; letter-spacing: .08em;
                       color: #1F4E79; font-weight: 700; margin-bottom: 6px; }
        .info-box .campo { display: flex; justify-content: space-between; padding: 2px 0;
                           border-bottom: 1px dotted #eee; font-size: 11px; }
        .info-box .campo:last-child { border-bottom: none; }
        .info-box .campo label { color: #666; }
        .info-box .campo span  { font-weight: 600; text-align: right; max-width: 60%; }

        /* Tabla de productos */
        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase;
                         letter-spacing: .08em; color: #1F4E79; margin-bottom: 8px;
                         padding-bottom: 4px; border-bottom: 1px solid #1F4E79; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
        th { background: #1F4E79; color: #fff; padding: 7px 8px; text-align: left; font-size: 11px; }
        th.center, td.center { text-align: center; }
        td { padding: 6px 8px; border-bottom: 1px solid #eee; font-size: 11px; }
        tr:nth-child(even) td { background: #f4f8fd; }
        tfoot td { font-weight: 700; background: #e8f0fe !important;
                   border-top: 2px solid #1F4E79; font-size: 12px; }

        /* Observaciones */
        .obs-box { border: 1px dashed #aaa; border-radius: 6px; padding: 10px;
                   min-height: 40px; margin-bottom: 16px; font-size: 11px; color: #444; }

        /* Firmas */
        .firmas { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px; margin-top: 24px; }
        .firma-box { border-top: 1px solid #333; padding-top: 6px; text-align: center; font-size: 10px; color: #555; }

        /* Footer */
        .doc-footer { text-align: center; margin-top: 20px; font-size: 10px; color: #aaa;
                      border-top: 1px solid #eee; padding-top: 8px; }

        /* Botones (solo pantalla) */
        .acciones { text-align: center; margin-bottom: 20px; }
        .btn-imp  { background: #1F4E79; color: #fff; border: none; padding: 9px 24px;
                    border-radius: 6px; font-size: 13px; font-weight: 600; cursor: pointer; margin: 0 6px; }
        .btn-imp:hover { background: #2E75B6; }
        .btn-cerrar { background: #6c757d; color: #fff; border: none; padding: 9px 24px;
                      border-radius: 6px; font-size: 13px; cursor: pointer; }

        @media print {
            .acciones { display: none !important; }
            body { padding: 8mm; }
        }
    </style>
</head>
<body>

<div class="acciones">
    <button class="btn-imp" onclick="window.print()">🖨 Imprimir</button>
    <button class="btn-cerrar" onclick="window.close()">✕ Cerrar</button>
</div>

<!-- Encabezado -->
<div class="header">
    <div class="marca">
        <h2>&#11041; InvControl</h2>
        <p>Sistema Web de Gestión de Inventarios</p>
        <p>http://localhost/invcontrol/</p>
    </div>
    <div class="orden-num">
        <div style="font-size:11px;color:#888;margin-bottom:2px">ORDEN DE DESPACHO</div>
        <div class="num"><?= htmlspecialchars($orden['numero']) ?></div>
        <div>
            <span class="estado estado-<?= $orden['estado'] ?>">
                <?= strtoupper($orden['estado']) ?>
            </span>
        </div>
    </div>
</div>

<!-- Info de la orden -->
<div class="info-grid">
    <div class="info-box">
        <h4>&#128100; Datos del cliente</h4>
        <div class="campo"><label>Nombre:</label><span><?= htmlspecialchars($orden['cliente']) ?></span></div>
        <div class="campo"><label>Teléfono:</label><span><?= htmlspecialchars($orden['telefono'] ?? '–') ?></span></div>
        <div class="campo"><label>Dirección:</label><span><?= htmlspecialchars($orden['direccion'] ?? '–') ?></span></div>
    </div>
    <div class="info-box">
        <h4>&#128203; Datos de la orden</h4>
        <div class="campo"><label>Número:</label><span><strong><?= htmlspecialchars($orden['numero']) ?></strong></span></div>
        <div class="campo"><label>Fecha creación:</label><span><?= date('d/m/Y H:i', strtotime($orden['fecha_creacion'])) ?></span></div>
        <?php if ($orden['fecha_despacho']): ?>
        <div class="campo"><label>Fecha despacho:</label><span><?= date('d/m/Y H:i', strtotime($orden['fecha_despacho'])) ?></span></div>
        <?php endif; ?>
        <div class="campo"><label>Registrado por:</label><span><?= htmlspecialchars($orden['usuario_nombre']) ?></span></div>
    </div>
</div>

<!-- Tabla de productos -->
<div class="section-title">&#128230; Productos despachados</div>
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>Código</th>
            <th>Producto</th>
            <th>Categoría</th>
            <th class="center">Cantidad</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($orden['detalle'] as $i => $d): ?>
        <tr>
            <td class="center"><?= $i+1 ?></td>
            <td><code><?= htmlspecialchars($d['producto_codigo']) ?></code></td>
            <td><strong><?= htmlspecialchars($d['producto_nombre']) ?></strong></td>
            <td><?= htmlspecialchars($d['categoria_nombre'] ?? '–') ?></td>
            <td class="center" style="font-size:14px;font-weight:700;color:#dc3545">
                <?= $d['cantidad'] ?>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="4" style="text-align:right">TOTAL UNIDADES DESPACHADAS:</td>
            <td class="center" style="color:#1F4E79;font-size:16px">
                <?= array_sum(array_column($orden['detalle'], 'cantidad')) ?>
            </td>
        </tr>
    </tfoot>
</table>

<!-- Observaciones -->
<div class="section-title">&#128172; Observaciones</div>
<div class="obs-box">
    <?= htmlspecialchars($orden['observacion'] ?? 'Sin observaciones.') ?>
</div>

<!-- Firmas -->
<div class="firmas">
    <div class="firma-box">
        Preparado por<br><br><br>
        <strong><?= htmlspecialchars($orden['usuario_nombre']) ?></strong><br>
        Almacén InvControl
    </div>
    <div class="firma-box">
        Recibido por (cliente)<br><br><br>
        <strong><?= htmlspecialchars($orden['cliente']) ?></strong><br>
        C.C.: ____________________
    </div>
    <div class="firma-box">
        Vo.Bo. Administrador<br><br><br>
        ____________________________<br>
        Firma y sello
    </div>
</div>

<!-- Footer del documento -->
<div class="doc-footer">
    Documento generado por InvControl – Sistema Web de Gestión de Inventarios |
    <?= date('d/m/Y H:i') ?> |
    Orden <?= htmlspecialchars($orden['numero']) ?>
</div>

<script>
// Auto-imprimir si viene con parámetro print=1
const params = new URLSearchParams(window.location.search);
if (params.get("autoprint") === "1") {
    window.addEventListener("load", () => setTimeout(() => window.print(), 600));
}
</script>
</body>
</html>
