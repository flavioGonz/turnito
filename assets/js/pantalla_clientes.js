// Config viene desde el PHP en window.PANTALLA_CONFIG
const CFG = window.PANTALLA_CONFIG || {};
const API = CFG.apiBase || '/turnero/api';
let lastCurrent = null;

async function fetchJSON(url){
  try{ const r = await fetch(url); return await r.json(); }
  catch(e){ return null; }
}

function label(prefix, n){ return `${prefix}-${String(n).padStart(4,'0')}`; }

function paintTable(tbody, rows, prefix, maxRows){
  let html = '';
  const list = (rows||[]).slice(0, maxRows);

  if(list.length){
    for(const n of list){ html += `<tr class="added"><td>${label(prefix,n)}</td></tr>`; }
    for(let k=list.length; k<maxRows; k++) html += `<tr><td style="opacity:.25">—</td></tr>`;
  }else{
    // placeholder si está vacío
    const base = (lastCurrent||0)+1;
    for(let i=0;i<maxRows;i++) html += `<tr><td style="opacity:.45">${label(prefix, base+i)}</td></tr>`;
  }

  if(tbody.dataset.html !== html){
    tbody.innerHTML = html;
    tbody.dataset.html = html;
  }
}

async function loadState(){
  const url = `${API}/queues_state.php?queue_id=${CFG.queueId}&hist_limit=${CFG.histMax}&next_limit=${CFG.nextMax}&_=${Date.now()}`;
  const j = await fetchJSON(url);
  if(!j || !j.ok) return;

  // número actual
  const currentTxt = label(j.prefix, j.current);
  const box = document.getElementById('turno');
  const span = box.querySelector('.digits');
  if(lastCurrent === null){ span.textContent = currentTxt; }
  else if(j.current !== lastCurrent){
    box.classList.remove('flip'); void box.offsetWidth;
    span.textContent = currentTxt;
    box.classList.add('flip');
  }
  lastCurrent = j.current;

  // tablas
  paintTable(document.querySelector('#tblNext tbody'), j.next, j.prefix, CFG.nextMax);
  paintTable(document.querySelector('#tblHist tbody'), (j.history||[]).filter(n=>n!==j.current), j.prefix, CFG.histMax);
}

function el(tag, cls){ const x=document.createElement(tag); if(cls) x.className=cls; return x; }

async function loadAds(){
  const ads = await fetchJSON(`${API}/ads.php`) || [];
  const box = document.getElementById('ads');
  if(!ads.length){ box.innerHTML = ''; return; }

  const nodes = ads.map(a=>{
    const wrap = el('div','adItem');
    let media;
    if(a.media_type==='video'){
      media = el('video');
      media.src = a.url; media.autoplay = true; media.loop = false; media.muted = true; media.playsInline = true;
      media.onended = next;
    }else{
      media = el('img'); media.src = a.url;
    }
    wrap.appendChild(media);
    box.appendChild(wrap);
    return {wrap, media, dur:(a.duration_sec||8)};
  });

  let i=0, timer=null;
  function show(k){
    nodes.forEach((n,idx)=> n.wrap.classList.toggle('active', idx===k));
    const n = nodes[k];
    clearTimeout(timer);
    if(n.media.tagName==='VIDEO'){
      n.media.currentTime=0; n.media.play().catch(()=>{});
      const fallback = Math.max(4, n.media.duration || n.dur) * 1000;
      timer = setTimeout(next, fallback);
    }else{
      timer = setTimeout(next, n.dur*1000);
    }
  }
  function next(){ i=(i+1)%nodes.length; show(i); }
  show(i);
}

export function boot(){
  loadState(); loadAds();
  setInterval(loadState, 1200);
}
