<?php
ini_set('display_errors','0'); error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

require __DIR__ . '/../db.php';

$queueId = max(1, (int)($_GET['queue_id'] ?? 1));

try {
  // lock de la queue
  $st = $pdo->prepare("SELECT prefix, pad FROM queues WHERE id=? FOR UPDATE");
  $pdo->beginTransaction();
  $st->execute([$queueId]);
  $q = $st->fetch();
  if (!$q) {
    $pdo->prepare("INSERT INTO queues (id,prefix,pad,current_number) VALUES (?,?,?,0)")
        ->execute([$queueId,'C',3]);
    $q = ['prefix'=>'C','pad'=>3];
  }

  // siguiente número
  $nx = $pdo->prepare("SELECT COALESCE(MAX(number),0)+1 AS nextn FROM tickets WHERE queue_id=?");
  $nx->execute([$queueId]);
  $nextn = (int)$nx->fetchColumn();

  $pdo->prepare("INSERT INTO tickets (queue_id, number, status) VALUES (?,?, 'waiting')")
      ->execute([$queueId, $nextn]);

  $pdo->commit();

  $pad = (int)$q['pad'];
  echo json_encode(['ok'=>true,'queue_id'=>$queueId,'number'=>$nextn,'prefix'=>$q['prefix'],'pad'=>$pad,'label'=>sprintf("%s-%0{$pad}d",$q['prefix'],$nextn)]);
} catch(Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
