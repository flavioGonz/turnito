<?php
require __DIR__.'/../includes/auth.php'; ensure_auth();
$cfg = require __DIR__.'/../includes/config.php';
require __DIR__.'/../includes/helpers.php';
ensure_dirs($cfg);

$op = $_POST['op'] ?? '';
$file = basename($_POST['file'] ?? '');
$scope = $_POST['scope'] ?? 'active';

$active = $cfg['STORAGE_ACTIVE'].'/'.$file;
$archive = $cfg['STORAGE_ARCHIVE'].'/'.$file;
$public = $cfg['PUBLIC_ADS_PATH'].'/'.$file;

switch ($op){
  case 'archive':
    if (is_file($active)) { rename($active, unique_path($cfg['STORAGE_ARCHIVE'],$file)); }
    if (is_file($public)) { unlink($public); }
    break;
  case 'restore':
    if (is_file($archive)) { 
      $dest = unique_path($cfg['STORAGE_ACTIVE'],$file);
      rename($archive, $dest);
      copy($dest, $cfg['PUBLIC_ADS_PATH'].'/'.basename($dest));
    }
    break;
  case 'delete':
    if ($scope==='active'){
      if (is_file($active)) unlink($active);
      if (is_file($public)) unlink($public);
    } else {
      if (is_file($archive)) unlink($archive);
    }
    break;
}
header('Location: index.php');
