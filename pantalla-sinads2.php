<?php
$queueId = intval($_GET['queue_id'] ?? 1);
$title   = $_GET['title'] ?? 'Carnicería';
$nextMax = max(1, intval($_GET['next'] ?? 10));
$logo    = $_GET['logo'] ?? '/turnero/assets/img/logo.png';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="refresh" content="7200">
  <meta name="viewport" content="width=device-width,initial-scale=1,viewport-fit=cover"/>
  <title>Pantalla (sin ads) - <?=htmlspecialchars($title)?></title>

  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&family=Oswald:wght@600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="/turnero/assets/css/pantalla.base.css">
  <link rel="stylesheet" href="/turnero/assets/css/pantalla.sinads.css">
</head>
<body class="screen single">

  <main class="solo">
    <header class="solo-header">
      <img class="logo" src="<?=htmlspecialchars($logo)?>" alt="logo" onerror="this.style.display='none'">
      <h1 class="area"><?=htmlspecialchars(strtoupper($title))?></h1>
    </header>

    <section class="solo-main">
      <div class="called">
        <h2 class="called-label">TURNO LLAMADO</h2>
        <div class="called-num" id="turno"><span class="digits"><span class="tens" id="prefix">C-</span><span class="num">000</span></span></div>
      </div>
      <div class="waiting compact">
        <ol id="waitCol1" class="waitList" aria-live="polite"></ol>
        <ol id="waitCol2" class="waitList" aria-live="polite"></ol>
      </div>
    </section>
  </main>

  <audio id="chime" src="/turnero/public/chime.mp3" preload="auto"></audio>

  <script>
  const QUEUE_ID  = <?= json_encode($queueId) ?>;
  const NEXT_MAX  = <?= json_encode($nextMax) ?>;
  const API_BASE  = '/turnero/api';
  const DISABLE_MARQUEE = true; // para pantalla.js
  </script>
  <script src="/turnero/assets/js/pantalla.js"></script>
</body>
</html>
