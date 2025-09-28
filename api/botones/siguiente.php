<?php
declare(strict_types=1);
// Require common helpers and enforce POST + local/auth for button endpoints
require __DIR__ . '/common.php';
require_post_and_auth();
require __DIR__ . '/../db.php';

$queueId = max(1, (int)($_POST['queue_id'] ?? $_GET['queue_id'] ?? 1));

try {
  $pdo->beginTransaction();

  // lock de la queue
  $st = $pdo->prepare("SELECT pad, current_number FROM queues WHERE id=? FOR UPDATE");
  $st->execute([$queueId]);
  $q = $st->fetch();
  if (!$q) throw new RuntimeException('Queue no existe');

  $pad = (int)$q['pad'];
  $cur = (int)$q['current_number'];

  // siguiente waiting > current
  $nx = $pdo->prepare("SELECT number FROM tickets WHERE queue_id=? AND status='waiting' AND number > ? ORDER BY number ASC LIMIT 1");
  $nx->execute([$queueId, $cur]);
  $row = $nx->fetch();
  if (!$row) {
    // no hay siguiente
    $pdo->commit();
    echo json_encode(['ok'=>true, 'message'=>'no-next', 'current'=>str_pad((string)$cur,$pad,'0',STR_PAD_LEFT)]);
    exit;
  }

  $next = (int)$row['number'];

  // marcar llamando y mover current
  $pdo->prepare("UPDATE tickets SET status='called' WHERE queue_id=? AND number=?")->execute([$queueId, $next]);
  $pdo->prepare("UPDATE queues  SET current_number=? WHERE id=?")->execute([$next, $queueId]);

  $pdo->commit();
  echo json_encode(['ok'=>true, 'current'=>$next, 'current_fmt'=>str_pad((string)$next,$pad,'0',STR_PAD_LEFT)]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
