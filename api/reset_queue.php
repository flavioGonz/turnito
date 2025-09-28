<?php
ini_set('display_errors','0'); error_reporting(0);
header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store');

require __DIR__ . '/../db.php';

$queueId = max(1, (int)($_GET['queue_id'] ?? 1));

try{
  // borrar tickets y poner current a 0
  $pdo->prepare("DELETE FROM tickets WHERE queue_id=?")->execute([$queueId]);
  $pdo->prepare("UPDATE queues SET current_number=0 WHERE id=?")->execute([$queueId]);
  echo json_encode(['ok'=>true]);
}catch(Throwable $e){
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
