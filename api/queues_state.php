<?php
ini_set('display_errors','0'); error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
require __DIR__ . '/../db.php';

$queueId   = max(1, (int)($_GET['queue_id'] ?? 1));
$nextLimit = max(0, (int)($_GET['next_limit'] ?? 10));

try {
  $st = $pdo->prepare("SELECT prefix, pad, current_number, logo FROM queues WHERE id=?");
  $st->execute([$queueId]);
  $q = $st->fetch();
  if (!$q) {
    $pdo->prepare("INSERT INTO queues (id,prefix,pad,current_number,logo) VALUES (?,?,?,?,NULL)")
        ->execute([$queueId, 'C', 3, 0]);
    $q = ['prefix'=>'C','pad'=>3,'current_number'=>0,'logo'=>null];
  }

  $prefix  = $q['prefix'];
  $pad     = (int)$q['pad'];
  $current = (int)$q['current_number'];
  $logo    = $q['logo']; // puede ser null

  $sql = "SELECT number FROM tickets WHERE queue_id=:qid AND status='waiting' AND number > :cur ORDER BY number ASC LIMIT :lim";
  $nx = $pdo->prepare($sql);
  $nx->bindValue(':qid',$queueId,PDO::PARAM_INT);
  $nx->bindValue(':cur',$current,PDO::PARAM_INT);
  $nx->bindValue(':lim',$nextLimit,PDO::PARAM_INT);
  $nx->execute();
  $next = array_map(fn($r)=>(int)$r['number'], $nx->fetchAll());

  echo json_encode([
    'ok'=>true,
    'queue_id'=>$queueId,
    'prefix'=>$prefix,
    'pad'=>$pad,
    'current'=>$current,
    'next'=>$next,
    'logo'=>$logo,
    'updated_at'=>time()
  ], JSON_UNESCAPED_SLASHES);
} catch(Throwable $e) {
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
