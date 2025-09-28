<?php
// Params
$queueId = max(1, (int)($_GET['queue_id'] ?? 1));
$title   = $_GET['title']   ?? 'Carnicería';
$nextMax = max(1, (int)($_GET['next'] ?? 10));
$ads     = (int)($_GET['ads'] ?? 1);               // 1 con publicidades, 0 sin
$aspect  = strtolower(trim($_GET['aspect'] ?? 'auto')); // auto | 16:9 | 9:16
$preset  = strtolower(trim($_GET['preset'] ?? 'tv'));   // tv | tablet | legacy
$scale   = (float)($_GET['scale'] ?? 1.0);         // ej 1.1 para overscan
$logo    = $_GET['logo'] ?? '/turnero/assets/img/logo.png';

function cls($cond, $name){ return $cond ? " $name" : ""; }

// Body classes
$bodyClasses  = "aspect-$aspect mode-".($ads? 'ads':'sinads')." preset-$preset";
$bodyClasses .= sprintf(" scale-%d", (int)round($scale*100));

// Flags
$IS_LEGACY = ($preset==='legacy');
$API_BASE  = '/turnero/api';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="refresh" content="7200">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
  <title><?=htmlspecialchars($title)?> – Pantalla</title>

  <!-- Fuentes -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Oswald:wght@600;700;800&display=swap" rel="stylesheet">

  <!-- Estilos -->
  <link rel="stylesheet" href="/turnero/assets/css/pantalla-base.css">
  <link rel="stylesheet" href="/turnero/assets/css/pantalla-enhanced.css">
  <link rel="stylesheet" href="/turnero/assets/css/pantalla-full.css">

  <?php if ($IS_LEGACY): ?>
  <link rel="stylesheet" href="/turnero/assets/css/pantalla-legacy.css">
  <?php endif; ?>

  <style>
    :root{ --scale: <?=$scale?>; }
  </style>
</head>
<body class="<?=$bodyClasses?>">
  <div class="stage"><!-- escala segura/overscan -->
    <div class="grid">
      <!-- Panel Izquierdo (turnos) -->
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

            <!-- Espera en dos columnas -->
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

      <!-- Panel Derecho (ads) -->
      <section class="panel right">
        <div class="rightInner">
          <div class="rightHeader">
            <div class="headline">OFERTA DEL DÍA!</div>
            <div class="menu">☰</div>
          </div>
          <div class="adColumn">
            <div class="adSlot top" id="adTop"><div class="adItem active"><img src="/turnero/public/media_ads_1.png" alt="ad"></div></div>
            <div class="adSlot bottom" id="adBottom"><div class="adItem"><img src="/turnero/public/media_ads_2.png" alt="ad"></div></div>
          </div>
        </div>
      </section>
    </div>
  </div>

  <!-- Sonido de alerta -->
  <audio id="chime" src="/turnero/public/chime.mp3" preload="auto"></audio>

<script>
// ====== Config del servidor ======
const QUEUE_ID = <?= json_encode($queueId) ?>;
const NEXT_MAX = <?= json_encode($nextMax) ?>;
const API_BASE = <?= json_encode($API_BASE) ?>;

// ====== Fix 100vh móviles (legacy friendly) ======
function setVH(){ document.documentElement.style.setProperty('--vh', (window.innerHeight*0.01)+'px'); }
setVH(); addEventListener('resize', setVH);

// ====== Orientación / aspect auto ======
(function(){
  const body = document.body;
  if(body.classList.contains('aspect-auto')){
    const p = window.matchMedia('(orientation: portrait)').matches;
    body.classList.toggle('aspect-9x16', p);
    body.classList.toggle('aspect-16x9', !p);
  }
})();

// ====== Lógica de estado ======
let lastCurrent = null;
const pad  = (n,p)=> String(n).padStart(p,'0');
const label= (pre,n,p)=> `${pre}-${pad(n,p)}`;

async function loadState(){
  try{
    const r = await fetch(`${API_BASE}/queues_state.php?queue_id=${QUEUE_ID}&next_limit=${NEXT_MAX}&_=${Date.now()}`, {cache:'no-store'});
    const j = await r.json(); if(!j.ok) return;
    const PAD = j.pad || 3;

    // número grande
    const numEl  = document.querySelector('#turno .num');
    const tensEl = document.querySelector('#turno .tens');
    if (tensEl) tensEl.textContent = j.prefix + '-';
    if (lastCurrent === null){ numEl.textContent = pad(j.current, PAD); }
    else if (j.current !== lastCurrent){
      numEl.textContent = pad(j.current, PAD);
      try{ const a=document.getElementById('chime'); if(a){ a.currentTime=0; a.play().catch(()=>{}); } }catch(e){}
    }
    lastCurrent = j.current;

    // listas espera
    const list = (j.next || []).slice(0, NEXT_MAX);
    const half = Math.ceil(list.length/2);
    const left = list.slice(0, half);
    const right= list.slice(half);
    const col1 = document.getElementById('waitCol1');
    const col2 = document.getElementById('waitCol2');
    const html1 = left.map(n=>`<li class="waitItem">${label(j.prefix,n,PAD)}</li>`).join('');
    const html2 = right.map(n=>`<li class="waitItem">${label(j.prefix,n,PAD)}</li>`).join('');
    if(col1 && col1.getAttribute('data-html') !== html1){ col1.innerHTML = html1; col1.setAttribute('data-html', html1); }
    if(col2 && col2.getAttribute('data-html') !== html2){ col2.innerHTML = html2; col2.setAttribute('data-html', html2); }
  }catch(e){}
}

// ====== Ads (solo si ads=1) ======
(function(){
  if (document.body.classList.contains('mode-ads')){
    fetch(`${API_BASE}/ads.php?ts=${Date.now()}`, {cache:'no-store'})
      .then(r=>r.json()).then(data=>{
        const items = Array.isArray(data) ? data : (data.items || []);
        const top = document.querySelector('#adTop .adItem img');
        const bot = document.querySelector('#adBottom .adItem img');
        if(items[0] && top){ top.src = items[0].url || items[0].image || top.src; }
        if(items[1] && bot){ bot.src = items[1].url || items[1].image || bot.src; }
      }).catch(()=>{});
  }
})();

addEventListener('load', ()=>{
  loadState();
  setInterval(loadState, 2500);
  try{ const a=document.getElementById('chime'); if(a){ a.play().catch(()=>{}); } }catch(e){}
});
</script>
</body>
</html>
