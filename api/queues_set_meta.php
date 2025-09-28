<?php
ini_set('display_errors','0'); error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');
require __DIR__ . '/../db.php';

$queueId = max(1, (int)($_POST['queue_id'] ?? $_GET['queue_id'] ?? 1));
$prefix  = isset($_POST['prefix']) || isset($_GET['prefix'])
  ? strtoupper(substr(preg_replace('/[^A-Z0-9]/i','', $_POST['prefix'] ?? $_GET['prefix']),0,3))
  : null;
$pad     = isset($_POST['pad']) || isset($_GET['pad'])
  ? max(2, min(4, (int)($_POST['pad'] ?? $_GET['pad'])))
  : null;
$logo    = isset($_POST['logo']) || isset($_GET['logo'])
  ? trim($_POST['logo'] ?? $_GET['logo'])
  : null;

try{
  // asegurar que la cola exista
  $st = $pdo->prepare("SELECT 1 FROM queues WHERE id=?"); $st->execute([$queueId]);
  if(!$st->fetch()){
    $pdo->prepare("INSERT INTO queues (id,prefix,pad,current_number,logo) VALUES (?,?,?,?,NULL)")
        ->execute([$queueId,'C',3,0]);
  }

  // construir UPDATE dinámico
  $sets = []; $args = [];
  if($prefix !== null){ $sets[]="prefix=?"; $args[]=$prefix; }
  if($pad !== null){    $sets[]="pad=?";    $args[]=$pad; }
  if($logo !== null){   $sets[]="logo=?";   $args[]=$logo; }
  if($sets){
    $args[] = $queueId;
    $sql = "UPDATE queues SET ".implode(',',$sets)." WHERE id=?";
    $pdo->prepare($sql)->execute($args);
  }

  // devolver meta actual
  $m = $pdo->prepare("SELECT prefix, pad, logo FROM queues WHERE id=?");
  $m->execute([$queueId]); $row = $m->fetch();
  echo json_encode(['ok'=>true,'queue_id'=>$queueId,'prefix'=>$row['prefix'],'pad'=>(int)$row['pad'],'logo'=>$row['logo']]);
}catch(Throwable $e){
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
