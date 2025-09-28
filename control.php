<?php // C:\xampp\htdocs\turnero\control.php ?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <title>Control - Turnero</title>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <style>
    :root{
      --bg:#0b0d11; --bg2:#0f1318; --panel:#0f1318; --panel2:#0b0f14;
      --txt:#e5e7eb; --muted:#9aa3af;
      --primary:#3b82f6; --ok:#19c37d; --warn:#f59e0b; --danger:#ef4444; --steel:#334155;
      --chip:#111827; --chipBorder:#1f2937; --shadow:0 14px 40px rgba(0,0,0,.35);
      --radius:16px;
    }
    *{box-sizing:border-box}
    html,body{height:100%}
    body{
      margin:0;background:linear-gradient(180deg,#090b10,#0b0d11);
      color:var(--txt); font-family:system-ui,-apple-system,Segoe UI,Roboto,Ubuntu,'Helvetica Neue',Arial;
      display:flex; flex-direction:column;
    }

    /* Topbar */
    .topbar{display:flex;align-items:center;gap:16px;padding:16px 20px;background:#111418;box-shadow:var(--shadow)}
    .brand{font-weight:700;letter-spacing:.3px}
    .tag{border:1px solid var(--steel);padding:6px 10px;border-radius:999px;color:var(--muted);font-size:.85rem}
    .spacer{flex:1}
    .status{display:flex;align-items:center;gap:8px;color:var(--muted);font-size:.9rem}
    .dot{width:10px;height:10px;border-radius:50%;}
    .dot.ok{background:var(--ok)} .dot.off{background:#6b7280} .dot.err{background:var(--danger)}

    /* Layout */
    .wrap{display:grid;grid-template-columns: 380px 1fr 380px;gap:18px;padding:18px;flex:1;min-height:0}
    .card{background:var(--panel);border:1px solid #1f2937;border-radius:var(--radius);box-shadow:var(--shadow);padding:16px}
    .card h3{margin:.2rem 0 1rem;font-size:1rem;color:var(--muted);font-weight:600;letter-spacing:.3px}

    /* Buttons */
    .btn{border:0;border-radius:14px;padding:12px 14px;cursor:pointer;font-size:1rem;display:flex;align-items:center;gap:10px;justify-content:center}
    .btn:disabled{opacity:.6;cursor:not-allowed}
    .btn .kbd{background:#1f2937;color:#cbd5e1;padding:.1rem .45rem;border-radius:6px;font-size:.8rem}
    .btn.primary{background:var(--primary);color:#fff}
    .btn.green{background:var(--ok);color:#000}
    .btn.orange{background:var(--warn);color:#000}
    .btn.gray{background:#94a3b8;color:#000}
    .btn.red{background:var(--danger);color:#fff}
    .btn.stretch{width:100%}

    .grid2{display:grid;grid-template-columns:1fr 1fr;gap:10px}
    .grid3{display:grid;grid-template-columns:repeat(3,1fr);gap:10px}

    /* Current big */
    .current{
      background:radial-gradient(1200px 400px at 50% -200px, rgba(59,130,246,.18), transparent), var(--panel2);
      border:1px solid #1f2937;border-radius:20px;padding:20px;text-align:center;
    }
    .current .label{color:var(--muted);font-size:.9rem;margin-bottom:4px}
    .current .digits{font-feature-settings:'tnum' 1, 'pnum' 1; font-variant-numeric:tabular-nums;
      font-size:4rem; font-weight:800; letter-spacing:2px; text-shadow:0 8px 24px rgba(0,0,0,.45)}
    .flip{animation:flip .5s ease}
    @keyframes flip{0%{transform:rotateX(0)}50%{transform:rotateX(90deg)}100%{transform:rotateX(0)}}

    /* Waiting chips */
    .rowTitle{color:var(--muted);margin:12px 2px 8px;font-size:.9rem}
    .chips{display:flex;flex-wrap:wrap;gap:8px}
    .chip{
      background:var(--chip);border:1px solid var(--chipBorder);border-radius:999px;
      padding:6px 10px; display:flex;align-items:center;gap:8px
    }
    .chip .dot{width:8px;height:8px;background:var(--primary)}
    .chip .num{font-variant-numeric:tabular-nums}

    /* Stats */
    .stat{background:var(--panel2);border:1px solid #1f2937;border-radius:14px;padding:12px}
    .stat .h{color:var(--muted);font-size:.8rem}
    .stat .v{font-size:1.4rem;font-weight:700}

    /* Log */
    .log{font-family:ui-monospace,Consolas,Menlo,monospace;font-size:.92rem;background:#0a0e12;border:1px solid #1f2937;border-radius:16px;
         padding:10px; height:100%; overflow:auto}
    .log .line{padding:6px 0;border-bottom:1px dashed #1f2937}
    .log .line:last-child{border-bottom:0}
    .muted{color:var(--muted)}

    /* Modal */
    .modal{position:fixed;inset:0;display:none;align-items:center;justify-content:center;background:rgba(0,0,0,.55);z-index:30}
    .modal .box{background:var(--panel);border:1px solid #1f2937;border-radius:16px;box-shadow:var(--shadow);width:min(560px,92vw);padding:18px}
    .modal .box h4{margin:.3rem 0 1rem}
    .modal.show{display:flex}
    .row{display:flex;gap:10px;align-items:center}
    .input{background:#0b0f14;color:var(--txt);border:1px solid #1f2937;border-radius:12px;padding:10px 12px;font-size:1rem}
    .switch{display:flex;align-items:center;gap:8px}

    /* Toasts */
    .toasts{position:fixed;right:18px;bottom:18px;display:flex;flex-direction:column;gap:10px;z-index:40}
    .toast{background:#111418;border:1px solid #1f2937;color:#e5e7eb;border-radius:12px;padding:10px 12px;box-shadow:var(--shadow);min-width:260px}
    .toast.ok{border-color:rgba(34,197,94,.4)} .toast.err{border-color:rgba(239,68,68,.5)}

    @media (max-width:1100px){
      .wrap{grid-template-columns:1fr;gap:14px}
      .grid3{grid-template-columns:1fr 1fr}
    }
  </style>
</head>
<body>

  <!-- TOPBAR -->
  <div class="topbar">
    <div class="brand">Control — Turnero</div>
    <span class="tag">Cola: <strong id="qName">#1</strong></span>
    <span class="tag">Prefijo: <strong id="qPrefix">C</strong></span>
    <span class="tag">Dígitos: <strong id="qPad">3</strong></span>
    <div class="spacer"></div>
    <div class="status"><span id="connDot" class="dot off"></span><span id="connText">Desconectado</span></div>
    <button id="openPrefs" class="btn gray">⚙️ Preferencias</button>
  </div>

  <!-- MAIN -->
  <div class="wrap">

    <!-- Columna izquierda: Acciones -->
    <div class="card">
      <h3>Acciones rápidas</h3>
      <div class="grid2">
        <button id="nuevo" class="btn green stretch">🧾 Sacar número <span class="kbd">Espacio</span></button>
        <button id="llamar" class="btn primary stretch">📣 Llamar (Siguiente) <span class="kbd">L</span></button>
      </div>
      <div class="grid2" style="margin-top:10px">
        <button id="servido" class="btn orange stretch">✅ Servido (auto-siguiente) <span class="kbd">S</span></button>
        <button id="atras" class="btn gray stretch">⬅️ Atrás (volver número) <span class="kbd">B</span></button>
      </div>
      <div class="grid2" style="margin-top:10px">
        <button id="reset" class="btn red stretch">🧨 Reset (a 0) <span class="kbd">R</span></button>
        <button id="help" class="btn gray stretch">❓ Atajos</button>
      </div>

      <div style="margin-top:14px" class="switch">
        <input type="checkbox" id="autoNext" />
        <label for="autoNext">Avanzar automáticamente al siguiente al marcar <b>Servido</b></label>
      </div>
      <div class="switch">
        <input type="checkbox" id="soundOn" />
        <label for="soundOn">Sonido al <b>llamar</b></label>
      </div>

      <div class="grid3" style="margin-top:14px">
        <div class="stat">
          <div class="h">Emitidos hoy</div>
          <div class="v" id="stEmi">—</div>
        </div>
        <div class="stat">
          <div class="h">En espera</div>
          <div class="v" id="stWait">—</div>
        </div>
        <div class="stat">
          <div class="h">Último ticket</div>
          <div class="v" id="stLast">—</div>
        </div>
      </div>
    </div>

    <!-- Columna central: Estado -->
    <div class="card">
      <h3>Estado de la cola</h3>
      <div class="current" id="curBox">
        <div class="label">Atendiendo</div>
        <div class="digits" id="curDigits">C-000</div>
      </div>

      <div class="rowTitle">Esperando</div>
      <div class="chips" id="chips"></div>
    </div>

    <!-- Columna derecha: Actividad -->
    <div class="card">
      <h3>Actividad</h3>
      <div id="log" class="log"></div>
    </div>
  </div>

  <!-- MODAL Preferencias -->
  <div class="modal" id="modalPrefs" aria-hidden="true">
    <div class="box">
      <h4>Preferencias</h4>
      <div class="row" style="margin-bottom:10px">
        <label style="width:140px">Cola (ID)</label>
        <input id="inQueue" type="number" class="input" min="1" step="1" value="1" style="width:140px">
      </div>
      <div class="row" style="margin-bottom:10px">
        <label style="width:140px">Prefijo</label>
        <input id="inPrefix" type="text" class="input" maxlength="3" placeholder="C" style="width:140px">
      </div>
      <div class="row" style="margin-bottom:10px">
        <label style="width:140px">Dígitos</label>
        <input id="inPad" type="number" class="input" min="2" max="4" value="3" style="width:140px">
      </div>
      <div class="row" style="margin-bottom:10px">
        <label style="width:140px">Máx. “Esperando”</label>
        <input id="inNextLimit" type="number" class="input" min="3" max="20" value="10" style="width:140px">
      </div>
      <div class="row" style="margin-top:16px;justify-content:flex-end;gap:8px">
        <button class="btn gray" id="closePrefs">Cancelar</button>
        <button class="btn primary" id="savePrefs">Guardar</button>
      </div>
      <div class="muted" style="margin-top:10px">Nota: los cambios de <b>prefijo</b> y <b>dígitos</b> requieren que el backend los soporte (si no, afectarán solo la visual).</div>
    </div>
  </div>

  <!-- MODAL Reset -->
  <div class="modal" id="modalReset" aria-hidden="true">
    <div class="box">
      <h4>Confirmar reset</h4>
      <p>¿Seguro que querés <b>resetear</b> la cola? Se reinicia a 0 y se borran los tickets en curso.</p>
      <div class="row" style="justify-content:flex-end;gap:8px;margin-top:8px">
        <button class="btn gray" id="cancelReset">Cancelar</button>
        <button class="btn red" id="confirmReset">Resetear</button>
      </div>
    </div>
  </div>

  <!-- Toasts -->
  <div class="toasts" id="toasts"></div>

<script>
/* ======= CONFIG ======= */
const API = '/turnero/api';
const PRINT_URL = '/turnero/print_ticket.php';
const LS = {
  queueId: 'ctrl.queueId', nextLimit:'ctrl.nextLimit', prefix:'ctrl.prefix', pad:'ctrl.pad',
  autoNext:'ctrl.autoNext', soundOn:'ctrl.soundOn'
};

/* ======= STATE ======= */
let QUEUE_ID = parseInt(localStorage.getItem(LS.queueId) || '1',10);
let NEXT_LIMIT = parseInt(localStorage.getItem(LS.nextLimit) || '10',10);
let PREFIX = localStorage.getItem(LS.prefix) || 'C';
let PAD = parseInt(localStorage.getItem(LS.pad) || '3',10);
let lastCurrent = null;
let emittedToday = 0;
let lastTicket = null;

/* ======= HELPERS ======= */
const $ = sel => document.querySelector(sel);
const logEl = $('#log');
function logLine(text, kind=''){
  const line = document.createElement('div'); line.className='line';
  const time = new Date().toLocaleTimeString();
  line.innerHTML = `<span class="muted">[${time}]</span> ${text}`;
  logEl.prepend(line);
}
function toast(msg, ok=true){
  const t = document.createElement('div');
  t.className='toast '+(ok?'ok':'err');
  t.textContent = msg;
  $('#toasts').appendChild(t);
  setTimeout(()=>t.remove(), 3000);
}
function pad(n,p){ return String(n).padStart(p,'0'); }
function label(pre, n, p){ return `${pre}-${pad(n,p)}`; }
function setConn(status){ // 'ok'|'off'|'err'
  const dot = $('#connDot'), tx = $('#connText');
  dot.className = 'dot '+status;
  tx.textContent = status==='ok' ? 'Conectado' : status==='err' ? 'Error' : 'Desconectado';
}

/* ======= UI BIND ======= */
$('#qName').textContent = '#'+QUEUE_ID;
$('#qPrefix').textContent = PREFIX;
$('#qPad').textContent = PAD;
$('#inQueue').value = QUEUE_ID;
$('#inPrefix').value = PREFIX;
$('#inPad').value = PAD;
$('#inNextLimit').value = NEXT_LIMIT;

$('#autoNext').checked = localStorage.getItem(LS.autoNext)==='1';
$('#soundOn').checked = localStorage.getItem(LS.soundOn)==='1';

/* ======= SOUNDS ======= */
const ding = new Audio('data:audio/mp3;base64,//uQZAAAAAAAAAAAAAAAAAAAA...'); // (silencioso/fake; sustituí por un mp3 real si querés)

/* ======= API ======= */
async function post(url){
  try{
    const r = await fetch(url, {method:'POST'});
    const j = await r.json();
    return j;
  }catch(e){ return null; }
}
async function getJSON(url){
  const r = await fetch(url, {cache:'no-store'});
  return await r.json();
}

/* ======= STATE REFRESH ======= */
async function refreshState(){
  try{
    const j = await getJSON(`${API}/queues_state.php?queue_id=${QUEUE_ID}&next_limit=${NEXT_LIMIT}&_=${Date.now()}`);
    if(!j || !j.ok){ setConn('err'); return; }
    setConn('ok');

    // actualiza prefijo/pad si backend los provee
    PREFIX = j.prefix || PREFIX; PAD = j.pad || PAD;
    $('#qPrefix').textContent = PREFIX; $('#qPad').textContent = PAD;

    // atendiendo
    const want = label(PREFIX, j.current, PAD);
    const cur = $('#curDigits');
    if(lastCurrent === null){ cur.textContent = want; }
    else if(j.current !== lastCurrent){
      const box = $('#curBox'); box.classList.remove('flip'); void box.offsetWidth;
      cur.textContent = want; box.classList.add('flip');
    }
    lastCurrent = j.current;

    // esperando
    const chips = $('#chips');
    const arr = (j.next || []).slice(0, NEXT_LIMIT);
    $('#stWait').textContent = arr.length;
    chips.innerHTML = arr.map(n=>`<div class="chip"><span class="dot"></span><span class="num">${label(PREFIX,n,PAD)}</span></div>`).join('');

    // stats
    $('#stEmi').textContent = emittedToday || '—';
    $('#stLast').textContent = lastTicket ? lastTicket : '—';
  }catch(e){
    setConn('err');
  }
}

/* ======= ACTIONS ======= */
async function sacarNumero(){
  const btn = $('#nuevo'); btn.disabled=true;
  const j = await post(`${API}/tickets.php?queue_id=${QUEUE_ID}`);
  btn.disabled=false;
  if(j?.ok){
    emittedToday++; lastTicket = label(j.prefix||PREFIX, j.number, j.pad||PAD);
    logLine(`Ticket emitido: <b>${lastTicket}</b>`);
    toast(`Ticket ${lastTicket} emitido`);
    window.open(`${PRINT_URL}?prefix=${encodeURIComponent(j.prefix||PREFIX)}&n=${encodeURIComponent(j.number)}`,'_blank');
    refreshState();
  } else {
    logLine(`Error al emitir: ${j?.error||'desconocido'}`,'err'); toast('Error al emitir',false);
  }
}

async function llamar(){
  const btn = $('#llamar'); btn.disabled=true;
  const j = await post(`${API}/queues_next.php?queue_id=${QUEUE_ID}`);
  btn.disabled=false;
  if(j?.ok){
    logLine(`📣 Llamado: <b>${label(j.prefix||PREFIX, j.current, j.pad||PAD)}</b>`);
    if($('#soundOn').checked){ try{ ding.currentTime=0; ding.play().catch(()=>{});}catch{} }
    toast('Llamado');
    refreshState();
  } else { logLine(`Error al llamar: ${j?.error||'desconocido'}`,'err'); toast('Error al llamar',false); }
}

async function servido(){
  const btn = $('#servido'); btn.disabled=true;
  const j = await post(`${API}/tickets_serve.php?queue_id=${QUEUE_ID}`);
  btn.disabled=false;
  if(j?.ok){
    const lbl = j.label || (j.current!=null ? label(j.prefix||PREFIX, j.current, j.pad||PAD) : 'Ticket');
    logLine(`✅ Servido: <b>${lbl}</b>${j.advanced?' · avanzó':''}`);
    toast('Marcado como servido');
    if($('#autoNext').checked && !j.advanced){
      await llamar();
    } else {
      refreshState();
    }
  } else { logLine(`Error al servir: ${j?.error||'desconocido'}`,'err'); toast('Error al servir',false); }
}

async function atras(){
  const btn = $('#atras'); btn.disabled=true;
  const j = await post(`${API}/queues_prev.php?queue_id=${QUEUE_ID}`);
  btn.disabled=false;
  if(j?.ok){
    logLine(`⬅️ Volvió a: <b>${label(j.prefix||PREFIX, j.current, j.pad||PAD)}</b>`);
    toast('Volvió al anterior');
    refreshState();
  } else { logLine(`Error al volver: ${j?.error||'desconocido'}`,'err'); toast('Error al volver',false); }
}

async function resetQueue(){
  const j = await post(`${API}/reset_queue.php?queue_id=${QUEUE_ID}`);
  if(j?.ok){ logLine('🧨 Sistema reseteado a 0'); toast('Reseteado'); lastCurrent=null; emittedToday=0; lastTicket=null; refreshState(); }
  else { logLine(`Error al resetear: ${j?.error||'desconocido'}`,'err'); toast('Error al resetear',false); }
}

/* ======= MODALS ======= */
function openModal(el){ el.classList.add('show'); el.setAttribute('aria-hidden','false'); }
function closeModal(el){ el.classList.remove('show'); el.setAttribute('aria-hidden','true'); }

$('#openPrefs').onclick = ()=> openModal($('#modalPrefs'));
$('#closePrefs').onclick = ()=> closeModal($('#modalPrefs'));
$('#savePrefs').onclick = ()=>{
  QUEUE_ID = parseInt($('#inQueue').value||'1',10);
  NEXT_LIMIT = parseInt($('#inNextLimit').value||'10',10);
  PREFIX = ($('#inPrefix').value||'C').toUpperCase().slice(0,3);
  PAD = Math.max(2, Math.min(4, parseInt($('#inPad').value||'3',10)));

  localStorage.setItem(LS.queueId, QUEUE_ID);
  localStorage.setItem(LS.nextLimit, NEXT_LIMIT);
  localStorage.setItem(LS.prefix, PREFIX);
  localStorage.setItem(LS.pad, PAD);

  $('#qName').textContent = '#'+QUEUE_ID;
  $('#qPrefix').textContent = PREFIX;
  $('#qPad').textContent = PAD;

  // Preferencias guardadas (lado cliente). Si tu backend soporta cambiar prefijo/pad, podés llamar a un endpoint aquí.
  closeModal($('#modalPrefs'));
  toast('Preferencias guardadas');
  refreshState();
};

$('#reset').onclick = ()=> openModal($('#modalReset'));
$('#cancelReset').onclick = ()=> closeModal($('#modalReset'));
$('#confirmReset').onclick = async ()=>{ closeModal($('#modalReset')); await resetQueue(); };

/* ======= TOGGLES ======= */
$('#autoNext').addEventListener('change', e=> localStorage.setItem(LS.autoNext, e.target.checked?'1':'0'));
$('#soundOn').addEventListener('change', e=> localStorage.setItem(LS.soundOn, e.target.checked?'1':'0'));

/* ======= SHORTCUTS ======= */
addEventListener('keydown', (e)=>{
  const k = e.key.toLowerCase();
  if(e.code==='Space'){ e.preventDefault(); sacarNumero(); }
  if(k==='l'){ e.preventDefault(); llamar(); }
  if(k==='s'){ e.preventDefault(); servido(); }
  if(k==='b'){ e.preventDefault(); atras(); }
  if(k==='r'){ e.preventDefault(); openModal($('#modalReset')); }
});
$('#help').onclick = ()=> {
  toast('Atajos: Espacio = Nuevo, L = Llamar, S = Servido, B = Atrás, R = Reset');
};

/* ======= BOOT ======= */
(async function init(){
  setConn('off');
  await refreshState();
  setInterval(refreshState, 1200);
})();
</script>
</body>
</html>
