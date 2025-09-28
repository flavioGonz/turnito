<?php
$queueId = intval($_GET['queue_id'] ?? 1);
$title   = $_GET['title'] ?? 'Carnicería';
$nextMax = max(1, intval($_GET['next'] ?? 10));
$logo    = $_GET['logo'] ?? '/turnero/assets/img/logo.png';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="refresh" content="7200">
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Pantalla - <?=htmlspecialchars($title)?></title>

  <!-- Fuentes + estilos base -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Oswald:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/turnero/assets/css/pantalla.css">
  <link rel="stylesheet" href="/turnero/assets/css/pantalla-sinads.css">
</head>
<body>
  <div class="grid">
    <!-- ÚNICO PANEL: Turnos (centrado) -->
    <section class="panel left">
      <div class="leftInner">
        <div class="leftHeader"><?=htmlspecialchars(strtoupper($title))?></div>

        <div class="centerArea">
          <div class="turnCenter">
            <h2 class="turnLabel">TURNO LLAMADO</h2>
            <div class="turnoArea">
              <div id="turno" class="turno">
                <span class="digits">
                  <span class="tens">C-</span><span class="num">000</span>
                </span>
              </div>
              <div class="dot-indicator" aria-hidden></div>
            </div>
          </div>

          <!-- Lista de espera en dos columnas -->
          <div class="waitWrap">
            <div class="waitCol labelsCol"></div>
            <div class="waitCol listCols">
              <div class="waitColInner">
                <div class="colLabel">EN FILA ESPERA:</div>
                <ol id="waitCol1" class="waitList" aria-live="polite"></ol>
              </div>
              <div class="waitColInner">
                <div class="colLabel">EN FILA</div>
                <ol id="waitCol2" class="waitList" aria-live="polite"></ol>
              </div>
            </div>
          </div>
        </div>

        <div class="leftFooter" aria-hidden></div>
      </div>
    </section>
  </div>

  <!-- campanita para cambio de número -->
  <audio id="chime" src="/turnero/public/chime.mp3" preload="auto"></audio>

<script>
const QUEUE_ID  = <?= json_encode($queueId) ?>;
const NEXT_MAX  = <?= json_encode($nextMax) ?>;
const API_BASE  = '/turnero/api';

let lastCurrent = null;
const pad = (n, p) => String(n).padStart(p,'0');
const label = (pre,n,p) => `${pre}-${pad(n,p)}`;

async function loadState(){
  try{
    const r = await fetch(`${API_BASE}/queues_state.php?queue_id=${QUEUE_ID}&next_limit=${NEXT_MAX}&_=${Date.now()}`);
    const j = await r.json(); if(!j.ok) return;
    const PAD = j.pad || 3;

    const numEl  = document.querySelector('#turno .num');
    const tensEl = document.querySelector('#turno .tens');
    if (tensEl) tensEl.textContent = j.prefix + '-';

    if(lastCurrent === null){ numEl.textContent = pad(j.current, PAD); }
    else if(j.current !== lastCurrent){
      numEl.textContent = pad(j.current, PAD);
      try{ const a=document.getElementById('chime'); if(a){ a.currentTime=0; a.play().catch(()=>{}); } }catch(e){}
    }
    lastCurrent = j.current;

    const list = (j.next || []).slice(0, NEXT_MAX);
    const half = Math.ceil(list.length/2);
    const left = list.slice(0, half);
    const right = list.slice(half);
    const col1 = document.getElementById('waitCol1');
    const col2 = document.getElementById('waitCol2');
    const html1 = left.map(n => `<li class="waitItem">${label(j.prefix,n,PAD)}</li>`).join('\n');
    const html2 = right.map(n => `<li class="waitItem">${label(j.prefix,n,PAD)}</li>`).join('\n');
    if(col1 && col1.getAttribute('data-html') !== html1){ col1.innerHTML = html1; col1.setAttribute('data-html', html1); }
    if(col2 && col2.getAttribute('data-html') !== html2){ col2.innerHTML = html2; col2.setAttribute('data-html', html2); }
  }catch(e){}
}

// Solo estado (sin marquee ni ads)
window.addEventListener('load', ()=>{
  loadState();
  setInterval(loadState, 2500);
  try{ const a=document.getElementById('chime'); if(a){ a.play().catch(()=>{}); } }catch(e){}
});
loadState();
setInterval(loadState, 1200);
</script>
</body>
</html>
