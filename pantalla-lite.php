<?php
$queueId = intval($_GET['queue_id'] ?? 1);
$title   = $_GET['title'] ?? 'Carnicería';
$nextMax = max(1, intval($_GET['next'] ?? 10));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="refresh" content="7200">
  <meta name="viewport" content="width=device-width,initial-scale=1"/>
  <title>Pantalla Lite - <?=htmlspecialchars($title)?></title>
  <style>
    html,body{margin:0;height:100%;font-family:Arial,Helvetica,sans-serif;background:#fff;color:#111}
    .wrap{display:flex;flex-direction:column;align-items:center;justify-content:center;height:100%;padding:12px;box-sizing:border-box}
    .title{font-size:28px;font-weight:bold;margin-bottom:6px;text-align:center}
    .called{font-size:20px;margin-top:8px}
    .num{font-size:96px;line-height:1;font-weight:bold;letter-spacing:2px}
    .waiting{display:flex;gap:16px;margin-top:12px;max-width:100vw}
    .waitList{list-style:none;padding:0;margin:0}
    .waitList li{font-size:22px;line-height:1.1}
  </style>
</head>
<body>
  <div class="wrap">
    <div class="title"><?=htmlspecialchars($title)?></div>
    <div class="called">Turno llamado</div>
    <div class="num" id="turno">C-000</div>
    <div class="waiting">
      <ol id="waitCol1" class="waitList"></ol>
      <ol id="waitCol2" class="waitList"></ol>
    </div>
  </div>
  <script>
    (function(){
      var API_BASE='/turnero/api', QUEUE_ID=<?= json_encode($queueId) ?>, NEXT_MAX=<?= json_encode($nextMax) ?>;
      function pad(n,p){return (Array(p).join('0')+n).slice(-p)}
      function label(pre,n,p){return pre+'-'+pad(n,p)}
      function q(){fetch(API_BASE+'/queues_state.php?queue_id='+QUEUE_ID+'&next_limit='+NEXT_MAX+'&_='+(+new Date()))
        .then(function(r){return r.json()}).then(function(j){
          if(!j||!j.ok){return}
          var PAD=j.pad||3, pre=j.prefix || 'C';
          document.getElementById('turno').textContent = label(pre, j.current, PAD);
          var list=(j.next||[]).slice(0,NEXT_MAX), half=Math.ceil(list.length/2);
          function render(id,arr){document.getElementById(id).innerHTML=arr.map(function(n){return '<li>'+label(pre,n,PAD)+'</li>'}).join('')}
          render('waitCol1', list.slice(0,half)); render('waitCol2', list.slice(half));
        })["catch"](function(){})}
      q(); setInterval(q, 2000);
    })();
  </script>
</body>
</html>
