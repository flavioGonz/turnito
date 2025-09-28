<?php
$prefix = preg_replace('/[^A-Z0-9\-]/i','', $_GET['prefix'] ?? 'C');
$n = intval($_GET['n'] ?? 0);
$ticket = sprintf("%s-%03d", $prefix, $n);
?><!doctype html>
<html>
<head>
  <meta charset="utf-8"/>
  <title><?php echo htmlspecialchars($ticket); ?></title>
  <style>
    @page { size: 58mm auto; margin: 4mm; }
    body { font-family: Arial, sans-serif; }
    .box { width: 50mm; text-align:center; }
    h1 { font-size: 28pt; margin: 8px 0; }
    .store { font-size: 10pt; }
    .small { font-size: 9pt; opacity: .8; }
    hr { border:0; border-top:1px dashed #555; margin:8px 0; }
  </style>
</head>
<body onload="setTimeout(()=>window.print(), 200)">
  <div class="box">
    <div class="store">CARNICERÍA</div>
    <hr>
    <h1><?php echo htmlspecialchars($ticket); ?></h1>
    <div class="small"><?php echo date('d/m/Y H:i'); ?></div>
    <hr>
    <div class="small">¡Gracias por su visita!</div>
  </div>
</body>
</html>
