<?php
// ============================================================
// InvControl – Chatbot de Guía Flotante
// Archivo: app/views/layouts/chatbot.php
// Incluir ANTES del cierre </body> en footer.php
// ============================================================
?>

<!-- ══════════════════════════════════════════════════════════
     CHATBOT FLOTANTE – INVCHAT
     ══════════════════════════════════════════════════════════ -->

<style>
/* ── Botón flotante ──────────────────────────────────────── */
#invChat-btn {
    position: fixed; bottom: 28px; right: 28px; z-index: 9999;
    width: 60px; height: 60px; border-radius: 50%;
    background: linear-gradient(135deg, #1F4E79, #2E75B6);
    border: none; box-shadow: 0 4px 18px rgba(31,78,121,.45);
    color: #fff; font-size: 1.6rem;
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; transition: transform .2s, box-shadow .2s;
    animation: pulse-btn 2.5s infinite;
}
#invChat-btn:hover { transform: scale(1.1); box-shadow: 0 6px 24px rgba(31,78,121,.6); }
@keyframes pulse-btn {
    0%,100% { box-shadow: 0 4px 18px rgba(31,78,121,.45); }
    50%      { box-shadow: 0 4px 28px rgba(31,78,121,.75); }
}
#invChat-badge {
    position: absolute; top: -4px; right: -4px;
    background: #e74c3c; color: #fff; border-radius: 50%;
    width: 20px; height: 20px; font-size: .65rem; font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    border: 2px solid #fff;
}

/* ── Ventana del chat ────────────────────────────────────── */
#invChat-window {
    position: fixed; bottom: 100px; right: 28px; z-index: 9998;
    width: 370px; max-height: 560px;
    background: #fff; border-radius: 18px;
    box-shadow: 0 12px 48px rgba(0,0,0,.22);
    display: none; flex-direction: column; overflow: hidden;
    animation: slideUp .25s ease;
}
@keyframes slideUp { from { opacity:0; transform: translateY(20px); } to { opacity:1; transform: translateY(0); } }

/* Header */
#invChat-header {
    background: linear-gradient(135deg, #1F4E79, #2E75B6);
    color: #fff; padding: 14px 16px;
    display: flex; align-items: center; gap: 10px;
}
#invChat-header .avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: rgba(255,255,255,.2);
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; flex-shrink: 0;
}
#invChat-header .info { flex: 1; }
#invChat-header .info strong { display: block; font-size: .95rem; }
#invChat-header .info small { opacity: .8; font-size: .75rem; }
#invChat-header .status-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #2ecc71; display: inline-block; margin-right: 4px;
}
#invChat-close {
    background: none; border: none; color: rgba(255,255,255,.8);
    font-size: 1.2rem; cursor: pointer; padding: 0 4px;
    transition: color .2s;
}
#invChat-close:hover { color: #fff; }

/* Sugerencias rápidas */
#invChat-suggestions {
    padding: 8px 12px; background: #f8f9ff;
    border-bottom: 1px solid #e8eaf0;
    display: flex; gap: 6px; flex-wrap: wrap;
}
#invChat-suggestions button {
    font-size: .72rem; padding: 4px 10px; border-radius: 20px;
    border: 1px solid #2E75B6; color: #2E75B6; background: #fff;
    cursor: pointer; transition: .2s; white-space: nowrap;
}
#invChat-suggestions button:hover { background: #2E75B6; color: #fff; }

/* Mensajes */
#invChat-messages {
    flex: 1; overflow-y: auto; padding: 14px 12px;
    display: flex; flex-direction: column; gap: 10px;
    background: #f4f6f9;
    scrollbar-width: thin; scrollbar-color: #ccc transparent;
}
.chat-msg { display: flex; gap: 8px; align-items: flex-end; }
.chat-msg.bot  { justify-content: flex-start; }
.chat-msg.user { justify-content: flex-end; }

.chat-msg .bot-avatar {
    width: 30px; height: 30px; border-radius: 50%;
    background: linear-gradient(135deg,#1F4E79,#2E75B6);
    color: #fff; display: flex; align-items: center; justify-content: center;
    font-size: .85rem; flex-shrink: 0;
}
.chat-bubble {
    max-width: 82%; padding: 10px 13px; border-radius: 16px;
    font-size: .83rem; line-height: 1.5; word-break: break-word;
}
.chat-msg.bot  .chat-bubble { background: #fff; color: #1a1a2e; border-bottom-left-radius: 4px; box-shadow: 0 1px 4px rgba(0,0,0,.08); }
.chat-msg.user .chat-bubble { background: linear-gradient(135deg,#1F4E79,#2E75B6); color: #fff; border-bottom-right-radius: 4px; }
.chat-bubble .chat-time { font-size: .68rem; opacity: .6; margin-top: 4px; display: block; }

/* Links dentro del chat */
.chat-bubble a { color: #2E75B6; text-decoration: underline; }
.chat-msg.user .chat-bubble a { color: #fff; }

/* Chips de opciones dentro del bot */
.chat-options { display: flex; flex-wrap: wrap; gap: 5px; margin-top: 7px; }
.chat-options button {
    font-size: .72rem; padding: 4px 10px; border-radius: 20px;
    border: 1px solid #2E75B6; color: #2E75B6; background: #fff;
    cursor: pointer; transition: .2s;
}
.chat-options button:hover { background: #2E75B6; color: #fff; }

/* Typing indicator */
.typing-indicator { display: flex; gap: 4px; padding: 8px 13px; }
.typing-indicator span {
    width: 7px; height: 7px; border-radius: 50%; background: #aaa;
    animation: typing .9s infinite;
}
.typing-indicator span:nth-child(2) { animation-delay: .15s; }
.typing-indicator span:nth-child(3) { animation-delay: .30s; }
@keyframes typing { 0%,60%,100% { transform: translateY(0); } 30% { transform: translateY(-6px); } }

/* Reporte card */
.report-card {
    background: linear-gradient(135deg,#e8f4fd,#f0f7ff);
    border: 1px solid #bee3f8; border-radius: 10px;
    padding: 10px 12px; margin-top: 6px; font-size: .8rem;
}
.report-card .report-title { font-weight: 700; color: #1F4E79; margin-bottom: 6px; font-size: .85rem; }
.report-card .report-row { display: flex; justify-content: space-between; padding: 2px 0; border-bottom: 1px solid #d8edf9; }
.report-card .report-row:last-child { border-bottom: none; }
.report-card .report-val { font-weight: 600; color: #1F4E79; }
.report-card .report-val.danger { color: #e74c3c; }
.report-card .report-val.success { color: #27ae60; }

/* Input area */
#invChat-input-area {
    padding: 10px 12px; background: #fff;
    border-top: 1px solid #e8eaf0;
    display: flex; gap: 8px; align-items: center;
}
#invChat-input {
    flex: 1; border: 1px solid #dde; border-radius: 22px;
    padding: 8px 14px; font-size: .83rem; outline: none;
    transition: border-color .2s;
}
#invChat-input:focus { border-color: #2E75B6; }
#invChat-send {
    width: 38px; height: 38px; border-radius: 50%;
    background: linear-gradient(135deg,#1F4E79,#2E75B6);
    border: none; color: #fff; font-size: 1rem;
    cursor: pointer; display: flex; align-items: center; justify-content: center;
    transition: transform .2s;
}
#invChat-send:hover { transform: scale(1.1); }
</style>

<!-- ── Botón flotante ──────────────────────────────────────── -->
<button id="invChat-btn" title="InvChat – Asistente InvControl" onclick="toggleChat()">
    <i class="bi bi-robot"></i>
    <span id="invChat-badge">1</span>
</button>

<!-- ── Ventana del chat ────────────────────────────────────── -->
<div id="invChat-window">
    <!-- Header -->
    <div id="invChat-header">
        <div class="avatar"><i class="bi bi-robot"></i></div>
        <div class="info">
            <strong>InvChat 🤖</strong>
            <small><span class="status-dot"></span>Asistente de InvControl</small>
        </div>
        <button id="invChat-close" onclick="toggleChat()" title="Cerrar">
            <i class="bi bi-x-lg"></i>
        </button>
    </div>

    <!-- Sugerencias rápidas -->
    <div id="invChat-suggestions">
        <button onclick="askQuick('¿Cómo registro una entrada?')">📥 Entrada</button>
        <button onclick="askQuick('¿Cómo registro una salida?')">📤 Salida</button>
        <button onclick="askQuick('Ver reporte de stock')">📊 Reporte</button>
        <button onclick="askQuick('¿Qué es el Kardex?')">📋 Kardex</button>
        <button onclick="askQuick('Ayuda general')">❓ Ayuda</button>
    </div>

    <!-- Mensajes -->
    <div id="invChat-messages"></div>

    <!-- Input -->
    <div id="invChat-input-area">
        <input type="text" id="invChat-input" placeholder="Escribe tu pregunta..."
               onkeypress="if(event.key==='Enter') sendMessage()">
        <button id="invChat-send" onclick="sendMessage()"><i class="bi bi-send-fill"></i></button>
    </div>
</div>

<script>
// ══════════════════════════════════════════════════════════════
// INVCHAT – Lógica completa del chatbot
// ══════════════════════════════════════════════════════════════

const APP_URL_JS = '<?= APP_URL ?>';

// ── Estado ────────────────────────────────────────────────────
let chatOpen   = false;
let isTyping   = false;
let msgCount   = 0;

// ── Toggle ventana ────────────────────────────────────────────
function toggleChat() {
    chatOpen = !chatOpen;
    const win = document.getElementById('invChat-window');
    const badge = document.getElementById('invChat-badge');
    win.style.display = chatOpen ? 'flex' : 'none';
    badge.style.display = 'none';
    if (chatOpen && msgCount === 0) {
        setTimeout(() => botWelcome(), 400);
    }
}

// ── Mensaje de bienvenida ─────────────────────────────────────
function botWelcome() {
    const nombre = '<?= htmlspecialchars($_SESSION['nombre'] ?? 'usuario') ?>';
    const rol    = '<?= $_SESSION['rol'] ?? 'operador' ?>';
    addBotMsg(
        `¡Hola <strong>${nombre}</strong>! 👋 Soy <strong>InvChat</strong>, tu asistente de InvControl.<br><br>` +
        `Puedo ayudarte con:<br>` +
        `<div class="chat-options">` +
        `<button onclick="askQuick('¿Cómo registro una entrada?')">📥 Registrar Entrada</button>` +
        `<button onclick="askQuick('¿Cómo registro una salida?')">📤 Registrar Salida</button>` +
        `<button onclick="askQuick('Ver reporte de stock')">📊 Reporte de Stock</button>` +
        `<button onclick="askQuick('¿Cómo busco un producto?')">🔍 Buscar Producto</button>` +
        `<button onclick="askQuick('¿Qué es el Kardex?')">📋 Kardex</button>` +
        `<button onclick="askQuick('Explicar los módulos')">🗂 Módulos</button>` +
        `</div>`
    );
}

// ── Enviar mensaje del usuario ────────────────────────────────
function sendMessage() {
    const input = document.getElementById('invChat-input');
    const text  = input.value.trim();
    if (!text || isTyping) return;
    input.value = '';
    addUserMsg(text);
    showTyping();
    setTimeout(() => {
        removeTyping();
        const resp = getResponse(text.toLowerCase());
        addBotMsg(resp);
    }, 700 + Math.random() * 400);
}

function askQuick(text) {
    document.getElementById('invChat-input').value = text;
    sendMessage();
}

// ── Agregar mensajes ──────────────────────────────────────────
function addUserMsg(text) {
    msgCount++;
    const msgs = document.getElementById('invChat-messages');
    msgs.innerHTML += `
        <div class="chat-msg user">
            <div class="chat-bubble">
                ${escapeHtml(text)}
                <span class="chat-time">${getTime()}</span>
            </div>
        </div>`;
    scrollBottom();
}

function addBotMsg(html) {
    msgCount++;
    const msgs = document.getElementById('invChat-messages');
    msgs.innerHTML += `
        <div class="chat-msg bot">
            <div class="bot-avatar"><i class="bi bi-robot"></i></div>
            <div class="chat-bubble">
                ${html}
                <span class="chat-time">${getTime()}</span>
            </div>
        </div>`;
    scrollBottom();
}

function showTyping() {
    isTyping = true;
    const msgs = document.getElementById('invChat-messages');
    msgs.innerHTML += `
        <div class="chat-msg bot" id="typing-msg">
            <div class="bot-avatar"><i class="bi bi-robot"></i></div>
            <div class="chat-bubble" style="padding:10px 14px">
                <div class="typing-indicator">
                    <span></span><span></span><span></span>
                </div>
            </div>
        </div>`;
    scrollBottom();
}

function removeTyping() {
    isTyping = false;
    const t = document.getElementById('typing-msg');
    if (t) t.remove();
}

function scrollBottom() {
    const msgs = document.getElementById('invChat-messages');
    msgs.scrollTop = msgs.scrollHeight;
}

function getTime() {
    return new Date().toLocaleTimeString('es-CO', { hour: '2-digit', minute: '2-digit' });
}

function escapeHtml(t) {
    return t.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
}

// ══════════════════════════════════════════════════════════════
// BASE DE CONOCIMIENTO DEL CHATBOT
// ══════════════════════════════════════════════════════════════
function getResponse(q) {

    // ── SALUDOS ──────────────────────────────────────────────
    if (match(q, ['hola','buenos','buenas','hey','hi','saludos'])) {
        return `¡Hola! 😊 ¿En qué puedo ayudarte hoy?
            <div class="chat-options">
                <button onclick="askQuick('Ayuda general')">❓ Ver opciones</button>
                <button onclick="askQuick('Ver reporte de stock')">📊 Reporte rápido</button>
            </div>`;
    }

    // ── ENTRADA ──────────────────────────────────────────────
    if (match(q, ['entrada','ingreso','llegó','llegaron','recibir','compra','recepción'])) {
        return `📥 <strong>Registrar Entrada de Inventario</strong><br><br>
            <strong>Pasos:</strong><br>
            1️⃣ Ve a <strong>Movimientos</strong> en el menú lateral<br>
            2️⃣ Haz clic en <strong>"Nueva Entrada"</strong><br>
            3️⃣ Selecciona el <strong>producto</strong><br>
            4️⃣ Ingresa la <strong>cantidad</strong> que llegó<br>
            5️⃣ Escribe una <strong>observación</strong> (Ej: Factura #001)<br>
            6️⃣ Clic en <strong>"Registrar Entrada"</strong><br><br>
            ✅ El stock se actualiza automáticamente.<br><br>
            <a href="${APP_URL_JS}/?page=movimientos&action=entrada">→ Ir a Nueva Entrada</a>`;
    }

    // ── SALIDA ───────────────────────────────────────────────
    if (match(q, ['salida','despacho','retiro','sacar','entregar','vendió'])) {
        return `📤 <strong>Registrar Salida de Inventario</strong><br><br>
            <strong>Pasos:</strong><br>
            1️⃣ Ve a <strong>Movimientos → Nueva Salida</strong><br>
            2️⃣ Selecciona el <strong>producto</strong> (verás el stock disponible)<br>
            3️⃣ Ingresa la <strong>cantidad</strong> a retirar<br>
            4️⃣ Agrega una <strong>observación</strong> (Ej: Pedido #456)<br>
            5️⃣ Clic en <strong>"Registrar Salida"</strong><br><br>
            ⚠️ Si la cantidad supera el stock disponible, el sistema te avisará y <strong>no realizará la operación</strong>.<br><br>
            <a href="${APP_URL_JS}/?page=movimientos&action=salida">→ Ir a Nueva Salida</a>`;
    }

    // ── REPORTE / STOCK ──────────────────────────────────────
    if (match(q, ['reporte','stock','existencia','inventario actual','cuánto hay','cuanto hay','saldo','resumen'])) {
        return buildReportCard();
    }

    // ── KARDEX ───────────────────────────────────────────────
    if (match(q, ['kardex','historial','movimientos de','seguimiento','trazabilidad'])) {
        return `📋 <strong>¿Qué es el Kardex?</strong><br><br>
            El Kardex es el <strong>historial completo</strong> de movimientos de cada producto: entradas, salidas y ajustes.<br><br>
            <strong>¿Cómo verlo?</strong><br>
            1️⃣ Ve a <strong>Productos</strong><br>
            2️⃣ En la fila del producto, haz clic en el ícono <strong>🕐 (reloj)</strong><br>
            3️⃣ Verás: fecha, tipo, cantidad, saldo y quién lo registró<br><br>
            💡 Puedes filtrar por rango de fechas.<br><br>
            <a href="${APP_URL_JS}/?page=productos">→ Ir a Productos</a>`;
    }

    // ── PRODUCTO ─────────────────────────────────────────────
    if (match(q, ['producto','crear producto','nuevo producto','agregar producto','registrar producto'])) {
        return `📦 <strong>Registrar Nuevo Producto</strong><br><br>
            1️⃣ Ve a <strong>Productos → Nuevo Producto</strong><br>
            2️⃣ Completa:<br>
            &nbsp;&nbsp;• <strong>Código</strong> único (Ej: PROD-007)<br>
            &nbsp;&nbsp;• <strong>Nombre</strong> del producto<br>
            &nbsp;&nbsp;• <strong>Categoría</strong> y <strong>Proveedor</strong><br>
            &nbsp;&nbsp;• <strong>Stock inicial</strong> y <strong>Stock mínimo</strong><br>
            3️⃣ Clic en <strong>Guardar</strong><br><br>
            ⚠️ El código no puede repetirse.<br><br>
            <a href="${APP_URL_JS}/?page=productos&action=nuevo">→ Crear Producto</a>`;
    }

    // ── BUSCAR PRODUCTO ──────────────────────────────────────
    if (match(q, ['buscar','busco','encontrar','cómo busco','como busco','filtrar'])) {
        return `🔍 <strong>Buscar un Producto</strong><br><br>
            En la pantalla de <strong>Productos</strong> hay una barra de búsqueda en la parte superior.<br><br>
            Puedes buscar por:<br>
            • <strong>Código</strong> del producto (Ej: PROD-001)<br>
            • <strong>Nombre</strong> del producto<br><br>
            La tabla se filtra <strong>en tiempo real</strong> mientras escribes. No necesitas presionar Enter.<br><br>
            <a href="${APP_URL_JS}/?page=productos">→ Ir a Productos</a>`;
    }

    // ── ALERTA STOCK BAJO ────────────────────────────────────
    if (match(q, ['alerta','bajo stock','stock bajo','mínimo','minimo','agotarse','agotando'])) {
        return `⚠️ <strong>Alertas de Stock Bajo</strong><br><br>
            El sistema resalta automáticamente los productos cuando su stock actual es <strong>menor o igual</strong> al stock mínimo.<br><br>
            <strong>¿Dónde verlo?</strong><br>
            • En el <strong>Dashboard</strong>: tarjeta "Bajo Stock" y tabla de alertas<br>
            • En <strong>Productos</strong>: filas en naranja/rojo con ícono ⚠<br>
            • En <strong>Reportes → Existencias</strong>: columna Estado<br><br>
            💡 Para resolver una alerta: registra una <strong>Entrada</strong> del producto.<br><br>
            <a href="${APP_URL_JS}/?page=reportes&action=existencias">→ Ver Reporte Existencias</a>`;
    }

    // ── MÓDULOS ──────────────────────────────────────────────
    if (match(q, ['módulo','modulo','módulos','modulos','explicar','qué tiene','que tiene','partes del sistema'])) {
        return `🗂 <strong>Módulos de InvControl</strong><br><br>
            <strong>📊 Dashboard</strong> — Resumen general: totales, alertas y accesos rápidos<br><br>
            <strong>📦 Productos</strong> — CRUD: crear, editar, eliminar y ver Kardex<br><br>
            <strong>↔️ Movimientos</strong> — Registrar entradas y salidas de inventario<br><br>
            <strong>🏷 Categorías</strong> — Clasificar los productos (solo Admin)<br><br>
            <strong>🚚 Proveedores</strong> — Gestionar proveedores (solo Admin)<br><br>
            <strong>📈 Reportes</strong> — Existencias y movimientos por fecha (solo Admin)<br><br>
            <div class="chat-options">
                <button onclick="askQuick('¿Cómo registro una entrada?')">📥 Entrada</button>
                <button onclick="askQuick('¿Cómo registro una salida?')">📤 Salida</button>
                <button onclick="askQuick('Ver reporte de stock')">📊 Reportes</button>
            </div>`;
    }

    // ── REPORTES DETALLADOS ──────────────────────────────────
    if (match(q, ['reporte movimiento','reporte de movimiento','movimientos por fecha','filtrar movimiento'])) {
        return `📈 <strong>Reporte de Movimientos por Fecha</strong><br><br>
            1️⃣ Ve a <strong>Reportes → Movimientos</strong><br>
            2️⃣ Selecciona el rango: <strong>Desde – Hasta</strong><br>
            3️⃣ Clic en <strong>Filtrar</strong><br>
            4️⃣ Verás: producto, tipo, cantidad, saldo, usuario y observación<br>
            5️⃣ Usa el botón <strong>🖨 Imprimir</strong> para guardar o imprimir<br><br>
            <a href="${APP_URL_JS}/?page=reportes&action=movimientos">→ Ir a Reporte Movimientos</a>`;
    }

    // ── USUARIOS / ROLES ─────────────────────────────────────
    if (match(q, ['rol','roles','admin','administrador','operador','permisos','acceso'])) {
        return `👥 <strong>Roles del Sistema</strong><br><br>
            <strong>🔑 Administrador</strong><br>
            Acceso completo: crear/editar/eliminar productos, categorías, proveedores, ver reportes y gestionar el sistema.<br><br>
            <strong>👤 Operador</strong><br>
            Solo puede: registrar entradas/salidas y consultar el stock de productos.<br><br>
            💡 Si necesitas más accesos, contacta al administrador del sistema.`;
    }

    // ── CERRAR SESIÓN ────────────────────────────────────────
    if (match(q, ['cerrar sesión','cerrar sesion','salir','logout','desconectar'])) {
        return `🚪 <strong>Cerrar Sesión</strong><br><br>
            En el menú lateral izquierdo, en la parte inferior, verás el botón <strong>"Salir"</strong>.<br><br>
            Haz clic y tu sesión se cerrará de forma segura.<br><br>
            ⚠️ Por seguridad, siempre cierra sesión cuando termines de usar el sistema.`;
    }

    // ── LOGIN / CONTRASEÑA ───────────────────────────────────
    if (match(q, ['contraseña','password','login','ingresar','acceder','olvidé','olvide'])) {
        return `🔐 <strong>Acceso al Sistema</strong><br><br>
            Para ingresar necesitas:<br>
            • <strong>Correo electrónico</strong> de tu cuenta<br>
            • <strong>Contraseña</strong> asignada<br><br>
            Si olvidaste tu contraseña, contacta al <strong>administrador</strong> para que la restablezca.<br><br>
            <strong>Credenciales de prueba:</strong><br>
            📧 admin@invcontrol.com / <strong>Admin123</strong><br>
            📧 operador@invcontrol.com / <strong>Oper123</strong>`;
    }

    // ── AYUDA GENERAL ────────────────────────────────────────
    if (match(q, ['ayuda','help','qué puedes','que puedes','qué haces','que haces','menú','menu','opciones'])) {
        return `❓ <strong>¿En qué te puedo ayudar?</strong><br><br>
            <div class="chat-options">
                <button onclick="askQuick('¿Cómo registro una entrada?')">📥 Entradas</button>
                <button onclick="askQuick('¿Cómo registro una salida?')">📤 Salidas</button>
                <button onclick="askQuick('Ver reporte de stock')">📊 Reporte Stock</button>
                <button onclick="askQuick('¿Qué es el Kardex?')">📋 Kardex</button>
                <button onclick="askQuick('Alertas de stock bajo')">⚠️ Alertas</button>
                <button onclick="askQuick('Explicar los módulos')">🗂 Módulos</button>
                <button onclick="askQuick('Roles del sistema')">👥 Roles</button>
                <button onclick="askQuick('Reporte de movimientos por fecha')">📈 Movimientos</button>
            </div>`;
    }

    // ── GRACIAS ──────────────────────────────────────────────
    if (match(q, ['gracias','thank','perfecto','excelente','genial','listo','entendí','entendi','ok'])) {
        return `¡Con gusto! 😊 Si tienes más preguntas, aquí estaré.<br><br>
            <div class="chat-options">
                <button onclick="askQuick('Ayuda general')">❓ Ver más opciones</button>
                <button onclick="askQuick('Ver reporte de stock')">📊 Reporte rápido</button>
            </div>`;
    }

    // ── RESPUESTA POR DEFECTO ─────────────────────────────────
    return `🤔 No entendí bien tu pregunta. ¿Puedo ayudarte con alguna de estas opciones?<br><br>
        <div class="chat-options">
            <button onclick="askQuick('¿Cómo registro una entrada?')">📥 Entradas</button>
            <button onclick="askQuick('¿Cómo registro una salida?')">📤 Salidas</button>
            <button onclick="askQuick('Ver reporte de stock')">📊 Reporte</button>
            <button onclick="askQuick('Explicar los módulos')">🗂 Módulos</button>
            <button onclick="askQuick('Ayuda general')">❓ Más ayuda</button>
        </div>`;
}

// ── Reporte de stock en tiempo real (lee desde PHP/DOM) ───────
function buildReportCard() {
    // Lee datos embebidos desde PHP
    const datos = window.invControlData || {};
    const total     = datos.total_productos    || '–';
    const bajoStock = datos.bajo_stock         || '–';
    const entradas  = datos.entradas_hoy       || '0';
    const salidas   = datos.salidas_hoy        || '0';
    const alertas   = datos.alertas            || [];

    let alertasHtml = '';
    if (alertas.length > 0) {
        alertasHtml = '<br><strong style="color:#e74c3c">⚠ Productos bajo stock:</strong><br>';
        alertas.slice(0,4).forEach(a => {
            alertasHtml += `<div class="report-row"><span>${a.nombre}</span><span class="report-val danger">${a.stock_actual}/${a.stock_minimo}</span></div>`;
        });
        if (alertas.length > 4) alertasHtml += `<small style="color:#999">...y ${alertas.length - 4} más</small>`;
    }

    return `📊 <strong>Reporte Rápido de Inventario</strong><br>
        <div class="report-card">
            <div class="report-title">📦 Estado actual del sistema</div>
            <div class="report-row"><span>Total productos</span><span class="report-val">${total}</span></div>
            <div class="report-row"><span>Bajo stock</span><span class="report-val ${bajoStock > 0 ? 'danger' : 'success'}">${bajoStock}</span></div>
            <div class="report-row"><span>Entradas hoy</span><span class="report-val success">+${entradas}</span></div>
            <div class="report-row"><span>Salidas hoy</span><span class="report-val danger">-${salidas}</span></div>
            ${alertasHtml}
        </div>
        <br><a href="${APP_URL_JS}/?page=reportes&action=existencias">→ Ver reporte completo</a>`;
}

// ── Utilidad: buscar palabras clave ───────────────────────────
function match(query, keywords) {
    return keywords.some(k => query.includes(k));
}
</script>

<?php
// ── Datos del sistema disponibles para el chatbot en JS ──────
$productoModel   = new Producto();
$movimientoModel = new Movimiento();
$totalProductos  = count($productoModel->getAll());
$bajoStock       = count($productoModel->getProductosBajoStock());
$resumenHoy      = $movimientoModel->getResumenHoy();
$alertas         = $productoModel->getProductosBajoStock();
?>
<script>
// Datos inyectados desde PHP para el reporte del chatbot
window.invControlData = {
    total_productos: <?= $totalProductos ?>,
    bajo_stock:      <?= $bajoStock ?>,
    entradas_hoy:    <?= (int)($resumenHoy['entradas_hoy'] ?? 0) ?>,
    salidas_hoy:     <?= (int)($resumenHoy['salidas_hoy']  ?? 0) ?>,
    alertas: <?= json_encode(array_map(fn($a) => [
        'nombre'       => $a['nombre'],
        'stock_actual' => $a['stock_actual'],
        'stock_minimo' => $a['stock_minimo'],
    ], $alertas)) ?>
};
</script>
