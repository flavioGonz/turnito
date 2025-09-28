<?php
// tools/gpio_monitor.php
// Simple web UI to monitor GPIO events reported to tools/gpio_report.php
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>GPIO Monitor</title>
  <style>
    body{font-family:Arial,Helvetica,sans-serif;background:#f7f7f8;color:#111;margin:0;padding:20px}
    .wrap{max-width:900px;margin:0 auto}
    h1{margin:0 0 10px}
    .last{font-size:18px;margin:12px 0;padding:12px;border-radius:8px;background:#fff;box-shadow:0 1px 3px rgba(0,0,0,0.06)}
    .events{margin-top:14px}
    .event{padding:8px 12px;border-bottom:1px solid #eee;background:#fff}
    .muted{color:#666;font-size:13px}
    .controls{margin-top:12px}
    button{padding:8px 12px;border-radius:6px;border:1px solid #ddd;background:#fff;cursor:pointer}
  </style>
</head>
<body>
  <div class="wrap">
    <h1>GPIO Monitor</h1>
    <div class="muted">Endpoint de ingestión: <code>tools/gpio_report.php</code> — Solo localhost por seguridad</div>

    <div id="last" class="last">No events yet</div>

    <div class="controls">
      <button id="refresh">Refresh</button>
      <button id="clearCache">Clear events file</button>
    </div>

    <div class="events" id="events"></div>
  </div>

<script>
async function fetchEvents(){
  try{
    const r = await fetch('gpio_events.json?_='+Date.now());
    if(!r.ok) throw new Error('HTTP '+r.status);
    const list = await r.json();
    render(list);
  }catch(e){
    document.getElementById('last').textContent = 'Error loading events: '+e.message;
    document.getElementById('events').innerHTML = '';
  }
}

function render(list){
  const last = list && list.length ? list[0] : null;
  document.getElementById('last').textContent = last ? `Last: pin ${last.pin} — ${last.value} @ ${last.ts_iso}` : 'No events yet';
  const ev = document.getElementById('events');
  ev.innerHTML = '';
  if(!list || !list.length) return;
  for(const e of list){
    const d = document.createElement('div'); d.className='event';
    d.innerHTML = `<strong>Pin ${e.pin}</strong> — ${e.value} <div class="muted">${new Date(e.ts*1000).toLocaleString()}</div>`;
    ev.appendChild(d);
  }
}

document.getElementById('refresh').addEventListener('click', fetchEvents);
document.getElementById('clearCache').addEventListener('click', async ()=>{
  if(!confirm('Clear events file?')) return;
  const r = await fetch('gpio_clear.php', {method:'POST'});
  if(r.ok) fetchEvents();
});

// auto-refresh
setInterval(fetchEvents, 1500);
fetchEvents();
</script>
</body>
</html>
