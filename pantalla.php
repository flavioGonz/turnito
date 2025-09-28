<?php
$queueId = intval($_GET['queue_id'] ?? 1);
$title   = $_GET['title'] ?? 'Carnicería';
$nextMax = max(1, intval($_GET['next'] ?? 10));
$logo    = $_GET['logo'] ?? '/turnero/assets/img/logo.png';
$marquee = $_GET['marquee'] ?? '▶ Bienvenido · Ofertas del día · Productos frescos · Calidad garantizada · ';
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="refresh" content="7200">
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Pantalla - <?=htmlspecialchars($title)?></title>

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Oswald:wght@600;700;800&display=swap" rel="stylesheet">
  <!-- New light layout stylesheet (matches the provided reference) -->
  <link rel="stylesheet" href="/turnero/assets/css/pantalla.css">
</head>
<body>
  <div class="grid">
    <!-- LEFT: Turnos (white panel) -->
    <section class="panel left">
      <div class="leftInner">
        <div class="leftHeader">CARNICERÍA</div>

        <div class="centerArea">
          <div class="turnCenter">
            <h2 class="turnLabel">TURNO LLAMADO</h2>
            <div class="turnoArea">
              <div id="turno" class="turno"><span class="digits"><span class="tens">C-</span><span class="num">000</span></span></div>
              <div class="dot-indicator" aria-hidden></div>
            </div>
          </div>

          <!-- Wait area: labels on the left, list on the right inside leftInner -->
          <div class="waitWrap">
              <div class="waitCol labelsCol">
                              </div>
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

        <!-- Marquee moved inside left panel -->
        <div class="ticker leftTicker"><div class="inner" id="tickText"><span class="marquee-track" aria-hidden><?=htmlspecialchars($marquee)?></span></div></div>

        <div class="leftFooter" aria-hidden>
        </div>
      </div>
    </section>

    <!-- RIGHT: Publicidad -->
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

  <!-- audio chime for new number -->
  <audio id="chime" src="/turnero/public/chime.mp3" preload="auto"></audio>

<script>
// ====== Variables inyectadas de forma segura ======
const QUEUE_ID  = <?= json_encode($queueId) ?>;
const NEXT_MAX  = <?= json_encode($nextMax) ?>;
const API_BASE  = '/turnero/api';
const FALLBACK_MARQUEE = <?= json_encode($marquee) ?>;

let lastCurrent = null;
const pad = (n, p) => String(n).padStart(p,'0');
const label = (pre,n,p) => `${pre}-${pad(n,p)}`;

function bootTicker(text){
  const t = document.getElementById('tickText');
  const msg = (text && String(text).trim().length) ? text : FALLBACK_MARQUEE;
  // Only update when content actually changes (user requested it be static and only change on new)
  if(t.getAttribute('data-last') === msg) return;
  t.setAttribute('data-last', msg);
  // set text inside container (no scrolling animation)
  t.innerHTML = `<span class="marquee-static">${msg}</span>`;
}

async function fetchMarquee(){
  try{
    const r = await fetch(`${API_BASE}/marquee.php?ts=${Date.now()}`, {cache:'no-store'});
    const d = await r.json();
    bootTicker(d.marquee || '');
    initMarquee();
  }catch(e){ bootTicker(''); }
}

async function loadState(){
  try{
    const r = await fetch(`${API_BASE}/queues_state.php?queue_id=${QUEUE_ID}&next_limit=${NEXT_MAX}&_=${Date.now()}`);
    const j = await r.json(); if(!j.ok) return;
    const PAD = j.pad || 3;

    // Atendiendo — render big number
    const wanted = label(j.prefix, j.current, PAD);
    const numEl = document.querySelector('#turno .num');
    if(lastCurrent === null){ numEl.textContent = pad(j.current, PAD); }
    else if(j.current !== lastCurrent){
      numEl.textContent = pad(j.current, PAD);
      // play audio chime when number changes (best-effort)
      try{ const a = document.getElementById('chime'); if(a){ a.currentTime = 0; a.play().catch(()=>{}); } }catch(e){}
      // visual flip handled by CSS animation class
  }
  lastCurrent = j.current;

  // Esperando — split into two continuous columns
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

async function loadAds(){
  // keep simple: use two slots and rotate once (server-driven)
  try{
    const r = await fetch(`${API_BASE}/ads.php?ts=${Date.now()}`, {cache:'no-store'});
    const data = await r.json();
    const items = Array.isArray(data) ? data : (data.items || []);
    if(!items.length) return;
    // Fill top and bottom sequentially
    const top = document.querySelector('#adTop .adItem img');
    const bottom = document.querySelector('#adBottom .adItem img');
    if(items[0]) top.src = items[0].url || items[0].image || top.src;
    if(items[1]) bottom.src = items[1].url || items[1].image || bottom.src;
  }catch(e){}
}

// Marquee: duplicate content and set animation duration to avoid jump/desync
function initMarquee(){
  const inner = document.getElementById('tickText');
  if(!inner) return;
  const track = inner.querySelector('.marquee-track');
  if(!track) return;
  // ensure there's enough content to loop smoothly by duplicating
  if(track.dataset.duplicated) return; // already initialized
  const text = track.textContent.trim();
  if(!text) return;
  // create second copy for seamless scroll
  const clone = track.cloneNode(true);
  clone.classList.add('clone');
  track.parentNode.appendChild(clone);
  track.dataset.duplicated = '1';

  // compute widths and set animation duration proportionally
  requestAnimationFrame(()=>{
    const trackWidth = track.offsetWidth + 80; // padding between repeats
    const containerWidth = inner.offsetWidth || 1360;
    // seconds: larger content scrolls longer; base speed ~120 px/s
    const duration = Math.max(6, Math.round((trackWidth + containerWidth) / 120));
    document.documentElement.style.setProperty('--marquee-duration', duration + 's');
    // add animate class
    track.classList.add('marquee-animate');
    clone.classList.add('marquee-animate');
  });
}

// Initialize on load
window.addEventListener('load', ()=>{ fetchMarquee(); loadState(); loadAds(); setInterval(loadState, 2500); setInterval(fetchMarquee, 15000); });

// Init
fetchMarquee();
setInterval(fetchMarquee, 15000);
loadState();
setInterval(loadState, 1200);
loadAds();

// Try to autoplay chime on load (best-effort). Browsers may block this.
window.addEventListener('load', ()=>{ try{ const a=document.getElementById('chime'); if(a){ a.play().catch(()=>{}); } }catch(e){} });
</script>
</body>
</html>
