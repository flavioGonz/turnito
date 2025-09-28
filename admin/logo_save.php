<?php
// /turnero/admin/logo_save.php
@session_start();
// require __DIR__.'/../includes/auth.php'; ensure_auth();

$logosDir = __DIR__.'/../storage/branding/logos';
$cfgFile  = __DIR__.'/../storage/branding/config.json';
@mkdir(dirname($cfgFile),0775,true);

$global = trim($_POST['global_logo'] ?? '');
$qLogo  = $_POST['q_logo'] ?? [];

$cfg = ['global_logo'=> $global ?: null, 'per_queue'=>[]];

if ($cfg['global_logo'] && !is_file($logosDir.'/'.$cfg['global_logo'])) $cfg['global_logo'] = null;

if (is_array($qLogo)) {
  foreach ($qLogo as $qid=>$fname) {
    $qid = (int)$qid;
    $fname = trim($fname);
    if ($fname && is_file($logosDir.'/'.$fname)) {
      $cfg['per_queue'][$qid] = $fname;
    }
  }
}

file_put_contents($cfgFile, json_encode($cfg, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT));
header('Location: index.php#tab-logos');
