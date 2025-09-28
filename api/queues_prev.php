<?php
ini_set('display_errors','0'); error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require __DIR__ . '/../db.php';

$queueId = max(1, (int)($_GET['queue_id'] ?? 1));

try{
  $pdo->beginTransaction();
  $st = $pdo->prepare("SELECT prefix, pad, current_number FROM queues WHERE id=? FOR UPDATE");
  $st->execute([$queueId]);
  $q = $st->fetch();
  if(!$q) throw new Exception('Queue not found');

  $current = (int)$q['current_number'];
  $pr = $pdo->prepare("SELECT MAX(number) FROM tickets WHERE queue_id=? AND number < ?");
  $pr->execute([$queueId, $current > 0 ? $current : PHP_INT_MAX]);
  $prev = (int)$pr->fetchColumn();

  if ($prev <= 0) { $pdo->commit(); echo json_encode(['ok'=>false,'error'=>'No hay anterior']); exit; }

  $pdo->prepare("UPDATE queues SET current_number=? WHERE id=?")->execute([$prev,$queueId]);
  $pdo->prepare("UPDATE tickets SET status='called', called_at=NOW() WHERE queue_id=? AND number=?")->execute([$queueId,$prev]);

  $pdo->commit();
  $pad = (int)$q['pad'];
  echo json_encode(['ok'=>true,'current'=>$prev,'prefix'=>$q['prefix'],'pad'=>$pad,'label'=>sprintf("%s-%0{$pad}d",$q['prefix'],$prev)]);
}catch(Throwable $e){
  if($pdo->inTransaction()) $pdo->rollBack();
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
