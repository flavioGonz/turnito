<?php
declare(strict_types=1);
// tools/gpio_clear.php — clear the events file
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
$allowed = in_array($remote, ['127.0.0.1', '::1'], true);
if (!$allowed) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Forbidden - only local clients allowed']);
    exit;
}

$dataFile = __DIR__ . '/gpio_events.json';
file_put_contents($dataFile, json_encode([], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true]);
