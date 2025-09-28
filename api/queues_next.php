<?php
ini_set('display_errors','0'); error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require __DIR__ . '/../db.php';

$queueId = max(1, (int)($_GET['queue_id'] ?? 1));

try {
  $pdo->beginTransaction();
  $st = $pdo->prepare("SELECT prefix, pad, current_number FROM queues WHERE id=? FOR UPDATE");
  $st->execute([$queueId]);
  $q = $st->fetch();
  if (!$q) throw new Exception('Queue not found');

  $current = (int)$q['current_number'];

  // primero waiting > current, si no hay toma el waiting más chico
  $nx = $pdo->prepare("SELECT number FROM tickets WHERE queue_id=? AND status='waiting' AND number>? ORDER BY number ASC LIMIT 1");
  $nx->execute([$queueId,$current]);
  $row = $nx->fetch();
  if (!$row) {
    $nx = $pdo->prepare("SELECT number FROM tickets WHERE queue_id=? AND status='waiting' ORDER BY number ASC LIMIT 1");
    $nx->execute([$queueId]);
    $row = $nx->fetch();
  }
  if (!$row) { $pdo->commit(); echo json_encode(['ok'=>false,'error'=>'No hay siguientes en espera']); exit; }

  $next = (int)$row['number'];
  $pdo->prepare("UPDATE queues SET current_number=? WHERE id=?")->execute([$next,$queueId]);
  $pdo->prepare("UPDATE tickets SET status='called', called_at=NOW() WHERE queue_id=? AND number=?")->execute([$queueId,$next]);

  $pdo->commit();

  $pad = (int)$q['pad'];
  echo json_encode(['ok'=>true,'current'=>$next,'prefix'=>$q['prefix'],'pad'=>$pad,'label'=>sprintf("%s-%0{$pad}d",$q['prefix'],$next)]);
} catch(Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
