<?php
// C:\xampp\htdocs\turnero\pantalla_clientes.php
$queueId = intval($_GET['queue_id'] ?? 1);
$title   = $_GET['title'] ?? 'Clientes';
$histMax = max(1, intval($_GET['hist'] ?? 12));
$nextMax = max(1, intval($_GET['next'] ?? 12));
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta http-equiv="refresh" content="7200">
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Pantalla Clientes - <?=htmlspecialchars($title)?></title>

  <!-- CSS separado -->
  <link rel="stylesheet" href="/turnero/assets/css/base.css">
  <link rel="stylesheet" href="/turnero/assets/css/pantalla-clientes.css">
</head>
<body>

  <!-- Sprite de íconos (SVG inline) -->
  <svg xmlns="http://www.w3.org/2000/svg" style="display:none">
    <symbol id="i-next" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <polyline points="9 18 15 12 9 6"></polyline>
    </symbol>
    <symbol id="i-history" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
      <polyline points="1 12 4 15 7 12"></polyline>
      <path d="M12 8v5l3 2"></path>
      <circle cx="12" cy="12" r="9"></circle>
    </symbol>
  </svg>

  <div class="grid">
    <!-- Panel turnos -->
    <section class="panel">
      <div class="pill"><?=htmlspecialchars($title)?></div>
      <div class="turnoWrap">
        <div class="label">Turno actual</div>
        <div id="turno" class="turno"><span class="digits">C-0000</span></div>

        <!-- Tablas -->
        <div class="tables">
          <div class="tableCard">
            <div class="tableHead">
              <svg class="icon"><use href="#i-next"/></svg> SIGUIENTES
            </div>
            <table id="tblNext"><tbody></tbody></table>
          </div>

          <div class="tableCard">
            <div class="tableHead">
              <svg class="icon"><use href="#i-history"/></svg> LLAMADOS
            </div>
            <table id="tblHist"><tbody></tbody></table>
          </div>
        </div>
      </div>
    </section>

    <!-- Panel publicidad -->
    <section class="panel">
      <div class="pill">Publicidad</div>
      <div class="adsWrap" id="ads"></div>
    </section>
  </div>

  <script>
    window.PANTALLA_CONFIG = {
      apiBase: '/turnero/api',
      queueId: <?=$queueId?>,
      histMax: <?=$histMax?>,
      nextMax: <?=$nextMax?>
    };
  </script>
  <script type="module">
    import { boot } from '/turnero/assets/js/pantalla_clientes.js';
    boot();
  </script>
</body>
</html>
