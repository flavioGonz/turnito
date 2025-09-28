<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');
error_reporting(0); ini_set('display_errors','0');

/* ===== Config ===== */
const BUTTON_KEY = '';            // si en api/botones/_common.php usás token, ponelo igual aquí
const DEFAULT_QUEUE_ID = 1;       // cola por defecto
$PIN_MAP = [                      // BCM pins → acción
  17 => 'nuevo',
  27 => 'siguiente',
  22 => 'anterior',
];
$LOG_FILE = __DIR__ . '/gpio_events.json';
/* ================== */

// Seguridad: solo localhost o token
$remote = $_SERVER['REMOTE_ADDR'] ?? '';
$localOk = in_array($remote, ['127.0.0.1', '::1'], true);
$hdrKey  = $_SERVER['HTTP_X_BUTTON_KEY'] ?? '';
$keyOk   = (BUTTON_KEY !== '' && hash_equals(BUTTON_KEY, $hdrKey));
if (!$localOk && !$keyOk) {
  http_response_code(403);
  echo json_encode(['ok'=>false,'error'=>'Forbidden']);
  exit;
}

// Cuerpo (JSON o form)
$raw = file_get_contents('php://input') ?: '';
$data = json_decode($raw, true);
if (!is_array($data)) {
  $data = [
    'pin'      => isset($_POST['pin']) ? (int)$_POST['pin'] : null,
    'value'    => $_POST['value'] ?? '',
    'queue_id' => isset($_POST['queue_id']) ? (int)$_POST['queue_id'] : null,
  ];
}

$pin   = isset($data['pin']) ? (int)$data['pin'] : null;
$value = (string)($data['value'] ?? '');
$qid   = isset($data['queue_id']) ? max(1,(int)$data['queue_id']) : DEFAULT_QUEUE_ID;

// Log (prepend, últimos 200)
$event = ['pin'=>$pin,'value'=>$value,'queue'=>$qid,'ts'=>time(),'ts_iso'=>date('c')];
$events = [];
if (is_file($LOG_FILE)) {
  $prev = json_decode((string)@file_get_contents($LOG_FILE), true);
  if (is_array($prev)) $events = $prev;
}
array_unshift($events, $event);
$events = array_slice($events, 0, 200);
@file_put_contents($LOG_FILE, json_encode($events, JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));

// ¿Debemos disparar acción? (pull-up: PRESIONADO = nivel 0 => "event:0")
$shouldTrigger = false;
if (strpos($value, 'event:') === 0) {
  $lvl = substr($value, 6);
  $shouldTrigger = ($lvl === '0');
} elseif ($value === 'pressed') {
  $shouldTrigger = true;
}

$action = $PIN_MAP[$pin] ?? null;
if (!$shouldTrigger || !$action) {
  echo json_encode(['ok'=>true,'note'=>'logged-only','event'=>$event,'action'=>$action]);
  exit;
}

// Forward a /api/botones/{accion}.php con queue_id
$path = "/turnero/api/botones/{$action}.php";
$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host   = $_SERVER['HTTP_HOST'] ?? '127.0.0.1';
$url    = $scheme . $host . $path;

$headers = ['Accept: application/json','Content-Type: application/x-www-form-urlencoded'];
if (BUTTON_KEY !== '') $headers[] = 'X-Button-Key: ' . BUTTON_KEY;

$ch = curl_init($url);
$body = http_build_query(['queue_id' => $qid], '', '&');
curl_setopt_array($ch, [
  CURLOPT_POST           => true,
  CURLOPT_POSTFIELDS     => $body,
  CURLOPT_HTTPHEADER     => $headers,
  CURLOPT_RETURNTRANSFER => true,
  CURLOPT_TIMEOUT        => 5,
]);
$resp = curl_exec($ch);
$err  = curl_error($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($resp === false) { http_response_code(502); echo json_encode(['ok'=>false,'error'=>'forward-failed','detail'=>$err]); exit; }
if ($code < 200 || $code >= 300) { http_response_code($code ?: 502); echo json_encode(['ok'=>false,'error'=>"upstream-$code",'body'=>trim((string)$resp)]); exit; }

$out = json_decode($resp, true);
echo json_encode(['ok'=>true,'event'=>$event,'action'=>$action,'result'=>$out]);