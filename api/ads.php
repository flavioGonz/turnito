<?php
// /turnero/api/ads.php
// Devuelve lista de publicidad desde /public/media/ads con metadatos y animación asignada
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$pubDir = __DIR__.'/../public/media/ads';
$animCfg = __DIR__.'/../storage/ads/animations.json';

// Detectar basePath (p.ej. /turnero)
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // /turnero/api
$basePath  = preg_replace('#/api$#','', $scriptDir);
if ($basePath === '/') $basePath = '';

$items = [];
$map = [];
if (is_file($animCfg)) {
  $tmp = json_decode(file_get_contents($animCfg), true);
  if (is_array($tmp)) $map = $tmp;
}

if (is_dir($pubDir)) {
  foreach (array_diff(scandir($pubDir), ['.','..']) as $f) {
    $path = $pubDir.'/'.$f;
    if (!is_file($path)) continue;
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    $type = in_array($ext, ['mp4','webm']) ? 'video' : 'image';

    $w = null; $h = null;
    if ($type === 'image') {
      try { [$w,$h] = getimagesize($path); } catch(Throwable $e){}
    }

    $url = $basePath.'/public/media/ads/'.rawurlencode($f);
    $anim = $type === 'image' ? ($map[$f] ?? 'kenburns') : null;

    $items[] = [
      'url' => $url,
      'type' => $type,
      'media_type' => $type,
      'duration_sec' => $type==='image' ? 8 : null,
      'size' => @filesize($path) ?: null,
      'mtime'=> @filemtime($path) ?: null,
      'w' => $w, 'h' => $h,
      'anim' => $anim
    ];
  }
  usort($items, fn($a,$b)=> ($b['mtime']??0) <=> ($a['mtime']??0));
}

echo json_encode(['items'=>$items, 'updated_at'=>time()], JSON_UNESCAPED_SLASHES);
