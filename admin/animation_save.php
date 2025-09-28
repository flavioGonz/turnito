<?php
// /turnero/admin/animation_save.php
@session_start();

$adsActive   = __DIR__.'/../storage/active';
$animCfgDir  = __DIR__.'/../storage/ads';
$animCfgFile = $animCfgDir.'/animations.json';
@mkdir($animCfgDir,0775,true);

$allowed = [
  'none','kenburns','pan_left','pan_right','pan_diagonal',
  'zoom_in','zoom_out','zoom_rotate',
  'tilt','pulse','slide_up','slide_left','flash',
  // nuevas
  'puffin','vanishin','swashing','foolishing','tunupin','tindownin',
  'random_soft','random_impact'
];

$file = trim($_POST['file'] ?? '');
$anim = trim($_POST['anim'] ?? '');

if ($file === '' || !in_array($anim, $allowed, true)) {
  header('Location: index.php#tab-activos'); exit;
}

$path = $adsActive.'/'.$file;
if (!is_file($path)) {
  if (preg_match('/[\/\\\\]/', $file)) { header('Location: index.php#tab-activos'); exit; }
}

$map = [];
if (is_file($animCfgFile)) {
  $tmp = json_decode(file_get_contents($animCfgFile), true);
  if (is_array($tmp)) $map = $tmp;
}
$map[$file] = $anim;

file_put_contents($animCfgFile, json_encode($map, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
header('Location: index.php#tab-activos');
