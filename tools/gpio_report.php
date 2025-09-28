<?php
declare(strict_types=1);
// tools/gpio_report.php
// Receives POST reports about GPIO events and stores them in a JSON file for monitoring.

// Only allow local requests by default
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
$allowed = in_array($remote, ['127.0.0.1', '::1'], true);
if (!$allowed) {
    http_response_code(403);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Forbidden - only local clients allowed']);
    exit;
}

// Read POST data (form or JSON)
$pin = null;
$value = null;

if (!empty($_POST)) {
    $pin = isset($_POST['pin']) ? (int)$_POST['pin'] : null;
    $value = $_POST['value'] ?? null;
} else {
    $raw = file_get_contents('php://input');
    if ($raw) {
        $json = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($json)) {
            $pin = isset($json['pin']) ? (int)$json['pin'] : $pin;
            $value = $json['value'] ?? $value;
        }
    }
}

if ($pin === null) {
    http_response_code(400);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['ok' => false, 'error' => 'Missing pin']);
    exit;
}

if ($value === null) $value = 'pressed';

$event = [
    'pin' => $pin,
    'value' => (string)$value,
    'ts' => time(),
    'ts_iso' => date('c')
];

$dataFile = __DIR__ . '/gpio_events.json';
$events = [];
if (file_exists($dataFile)) {
    $raw = file_get_contents($dataFile);
    $decoded = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
        $events = $decoded;
    }
}

// prepend event
array_unshift($events, $event);
// keep last 100
$events = array_slice($events, 0, 100);

file_put_contents($dataFile, json_encode($events, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

header('Content-Type: application/json; charset=utf-8');
echo json_encode(['ok' => true, 'event' => $event]);
