<?php
declare(strict_types=1);
// Require common helpers and enforce POST + local/auth for button endpoints
require __DIR__ . '/common.php';
require_post_and_auth();
require __DIR__ . '/../db.php';

$queueId = max(1, (int)($_POST['queue_id'] ?? $_GET['queue_id'] ?? 1));

try {
  $pdo->beginTransaction();

  $st = $pdo->prepare("SELECT pad, current_number FROM queues WHERE id=? FOR UPDATE");
  $st->execute([$queueId]);
  $q = $st->fetch();
  if (!$q) throw new RuntimeException('Queue no existe');

  $pad = (int)$q['pad'];
  $cur = (int)$q['current_number'];

  if ($cur <= 0) {
    $pdo->commit();
    echo json_encode(['ok'=>true, 'current'=>0, 'current_fmt'=>str_pad('0',$pad,'0',STR_PAD_LEFT)]);
    exit;
  }

  // último llamado menor al actual
  $pr = $pdo->prepare("SELECT number FROM tickets WHERE queue_id=? AND status='called' AND number < ? ORDER BY number DESC LIMIT 1");
  $pr->execute([$queueId, $cur]);
  $row = $pr->fetch();

  if ($row) {
    $prev = (int)$row['number'];
    // devolver a waiting y mover current
    $pdo->prepare("UPDATE tickets SET status='waiting' WHERE queue_id=? AND number=?")->execute([$queueId, $prev]);
    $pdo->prepare("UPDATE queues  SET current_number=? WHERE id=?")->execute([$prev, $queueId]);
    $pdo->commit();
    echo json_encode(['ok'=>true, 'current'=>$prev, 'current_fmt'=>str_pad((string)$prev,$pad,'0',STR_PAD_LEFT)]);
  } else {
    // no hay llamados previos -> current a 0
    $pdo->prepare("UPDATE queues SET current_number=0 WHERE id=?")->execute([$queueId]);
    $pdo->commit();
    echo json_encode(['ok'=>true, 'current'=>0, 'current_fmt'=>str_pad('0',$pad,'0',STR_PAD_LEFT)]);
  }
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
