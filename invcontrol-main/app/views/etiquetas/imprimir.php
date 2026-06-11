<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Etiquetas – InvControl</title>
    <!-- JsBarcode: genera el código de barras en el navegador, sin instalar nada -->
    <script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.6/dist/JsBarcode.all.min.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; background: #f0f0f0; padding: 20px; }

        .page-header {
            background: #1F4E79; color: #fff;
            padding: 12px 20px; border-radius: 8px; margin-bottom: 20px;
            display: flex; justify-content: space-between; align-items: center;
        }
        .page-header h4 { margin: 0; }

        .etiquetas-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
        }

        /* ── Etiqueta individual ─────────────────────────────── */
        .etiqueta {
            background: #fff;
            border: 1px solid #ccc;
            border-radius: 8px;
            padding: 14px 12px;
            display: flex;
            flex-direction: column;
            align-items: center;
            box-shadow: 0 2px 8px rgba(0,0,0,.1);
            page-break-inside: avoid;
        }

        .etiqueta .marca {
            font-size: .65rem;
            font-weight: 700;
            color: #1F4E79;
            letter-spacing: .12em;
            text-transform: uppercase;
            margin-bottom: 4px;
            align-self: flex-start;
        }

        .etiqueta .nombre-prod {
            font-size: .82rem;
            font-weight: 700;
            color: #1a1a2e;
            text-align: center;
            margin-bottom: 10px;
            line-height: 1.3;
            min-height: 34px;
            display: flex;
            align-items: center;
        }

        .etiqueta svg {
            max-width: 100%;
            height: auto;
        }

        .etiqueta .codigo-texto {
            font-family: monospace;
            font-size: .78rem;
            color: #444;
            margin-top: 6px;
            letter-spacing: .08em;
        }

        .etiqueta .info-extra {
            display: flex;
            justify-content: space-between;
            width: 100%;
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px dashed #ddd;
            font-size: .65rem;
            color: #666;
        }

        /* ── Sin código de barras del proveedor ─────────────── */
        .badge-generado {
            font-size: .6rem;
            background: #e8f4fd;
            color: #1F4E79;
            border: 1px solid #bee3f8;
            border-radius: 20px;
            padding: 1px 7px;
            margin-bottom: 4px;
            align-self: flex-end;
        }

        /* ── Botones de acción ───────────────────────────────── */
        .acciones {
            text-align: center;
            margin-bottom: 20px;
        }
        .btn-accion {
            display: inline-block;
            padding: 9px 22px;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            font-size: .9rem;
            border: none;
            margin: 0 6px;
            text-decoration: none;
        }
        .btn-imprimir { background: #1F4E79; color: #fff; }
        .btn-imprimir:hover { background: #2E75B6; }
        .btn-cerrar { background: #6c757d; color: #fff; }
        .btn-cerrar:hover { background: #5a6268; }

        /* ── Print media ─────────────────────────────────────── */
        @media print {
            body { background: #fff; padding: 5mm; }
            .page-header, .acciones { display: none !important; }
            .etiquetas-grid {
                grid-template-columns: repeat(3, 1fr);
                gap: 6mm;
            }
            .etiqueta {
                border: 1px solid #999;
                box-shadow: none;
                padding: 6px 8px;
                border-radius: 4px;
            }
        }
    </style>
</head>
<body>

<div class="page-header">
    <h4><i>InvControl</i> – Etiquetas con Código de Barras</h4>
    <small><?= date('d/m/Y H:i') ?></small>
</div>

<div class="acciones">
    <button class="btn-accion btn-imprimir" onclick="window.print()">
        🖨 Imprimir etiquetas
    </button>
    <button class="btn-accion btn-cerrar" onclick="window.close()">
        ✕ Cerrar
    </button>
</div>

<div class="etiquetas-grid" id="contenedorEtiquetas">
<?php
// $productos ya viene preparado desde ScannerController::imprimirEtiqueta()
foreach ($productos as $p):
    // Usar código de barras del proveedor si existe, sino usar el código interno
    $codigoBarras  = !empty($p['codigo_barras']) ? $p['codigo_barras'] : $p['codigo'];
    $tieneBarras   = !empty($p['codigo_barras']);
    $svgId         = 'barcode_' . $p['id'];
?>
    <div class="etiqueta etiqueta-print">
        <div class="marca">InvControl</div>
        <?php if (!$tieneBarras): ?>
            <span class="badge-generado">Código generado</span>
        <?php endif; ?>
        <div class="nombre-prod"><?= htmlspecialchars($p['nombre']) ?></div>

        <!-- SVG donde se dibujará el código de barras -->
        <svg id="<?= $svgId ?>" class="barcode-svg"
             data-codigo="<?= htmlspecialchars($codigoBarras) ?>">
        </svg>

        <span class="codigo-texto"><?= htmlspecialchars($codigoBarras) ?></span>

        <div class="info-extra">
            <span><?= htmlspecialchars($p['codigo']) ?></span>
            <span>Stock: <?= $p['stock_actual'] ?></span>
        </div>
    </div>
<?php endforeach; ?>

<?php if (empty($productos)): ?>
    <div style="grid-column:1/-1; text-align:center; padding:40px; color:#666">
        No se encontraron productos para imprimir.
    </div>
<?php endif; ?>
</div><!-- /#contenedorEtiquetas -->

<script>
// Generar todos los códigos de barras con JsBarcode
document.querySelectorAll(".barcode-svg").forEach(function(svg) {
    const codigo = svg.dataset.codigo;
    try {
        JsBarcode(svg, codigo, {
            format:      "CODE128",   // Universal, funciona con cualquier pistolera
            width:       1.8,
            height:      55,
            displayValue: false,      // Lo mostramos manualmente abajo
            margin:      4,
            background:  "#ffffff",
            lineColor:   "#000000",
        });
    } catch(e) {
        // Si el código tiene caracteres inválidos, usar CODABAR
        try {
            JsBarcode(svg, codigo, { format: "CODE39", width: 1.5, height: 50, displayValue: false });
        } catch(e2) {
            svg.innerHTML = `<text x="50%" y="50%" text-anchor="middle" fill="red" font-size="10">Código inválido</text>`;
        }
    }
});

// Auto-imprimir si viene con parámetro print=1
const params = new URLSearchParams(window.location.search);
if (params.get("print") === "1") {
    window.addEventListener("load", () => setTimeout(() => window.print(), 800));
}
</script>
</body>
</html>