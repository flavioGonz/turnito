<?php
declare(strict_types=1);
// Require common helpers and enforce POST + local/auth for button endpoints
require __DIR__ . '/common.php';
require_post_and_auth();
require __DIR__ . '/../db.php';

$queueId = max(1, (int)($_POST['queue_id'] ?? $_GET['queue_id'] ?? 1));

try {
  $pdo->beginTransaction();

  // lock de la queue para concurrencia
  $st = $pdo->prepare("SELECT pad FROM queues WHERE id=? FOR UPDATE");
  $st->execute([$queueId]);
  $row = $st->fetch();
  if (!$row) {
    // crea la queue si no existe
    $pdo->prepare("INSERT INTO queues (id,prefix,pad,current_number,logo) VALUES (?,?,?,?,NULL)")
        ->execute([$queueId, 'C', 3, 0]);
    $pad = 3;
  } else {
    $pad = (int)$row['pad'];
  }

  // próximo número (máximo actual + 1, con wrap a 1 en 999)
  $mx = (int)$pdo->query("SELECT COALESCE(MAX(number),0) FROM tickets WHERE queue_id=".$pdo->quote($queueId))->fetchColumn();
  $next = ($mx % 999) + 1;

  $ins = $pdo->prepare("INSERT INTO tickets (queue_id, number, status, created_at) VALUES (?,?, 'waiting', NOW())");
  $ins->execute([$queueId, $next]);

  $pdo->commit();
  echo json_encode(['ok'=>true, 'queue_id'=>$queueId, 'numero'=>str_pad((string)$next, $pad, '0', STR_PAD_LEFT), 'number'=>$next]);
} catch (Throwable $e) {
  if ($pdo->inTransaction()) $pdo->rollBack();
  echo json_encode(['ok'=>false,'error'=>$e->getMessage()]);
}
