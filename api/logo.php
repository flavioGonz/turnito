<?php
// /turnero/api/logo.php
header('Content-Type: application/json; charset=utf-8');

$queueId = max(1, (int)($_GET['queue_id'] ?? 1));
$cfgFile = __DIR__.'/../storage/branding/config.json';
$logosPubRel = '/public/media/logos';

// Detectar basePath (p.ej. /turnero)
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // /turnero/api
$basePath  = preg_replace('#/api$#','', $scriptDir);
if ($basePath === '/') $basePath = '';

$cfg = ['global_logo'=>null, 'per_queue'=>[]];
if (is_file($cfgFile)) {
  $tmp = json_decode(file_get_contents($cfgFile), true);
  if (is_array($tmp)) $cfg = array_merge($cfg, $tmp);
}

$fname = $cfg['per_queue'][$queueId] ?? $cfg['global_logo'] ?? null;
$url   = $fname ? ($basePath . $logosPubRel . '/' . rawurlencode($fname)) : null;

echo json_encode(['ok'=>true,'queue_id'=>$queueId,'url'=>$url], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE);
