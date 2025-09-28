<?php
declare(strict_types=1);
// Require common helpers and enforce POST + local/auth for button endpoints
require __DIR__ . '/common.php';
require_post_and_auth();
require __DIR__ . '/../db.php';

$queueId = max(1, (int)($_POST['queue_id'] ?? $_GET['queue_id'] ?? 1));

try {
  $pdo->beginTransaction();
  $pdo->prepare("UPDATE queues  SET current_number=0 WHERE id=?")->execute([$queueId]);
  $pdo->prepare("UPDATE tickets SET status='waiting' WHERE queue_id=? AND status<>'waiting'")->execute([$queueId]);
  $pdo->commit();
  echo json_encode(['ok'=>true,'reset'=>true,'queue_id'=>$queueId]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
