<?php
declare(strict_types=1);

/* Salida JSON limpia */
if (function_exists('ob_end_clean')) @ob_end_clean();
ob_start();
header_remove();
header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
ini_set('display_errors','0');

const BUTTON_KEY = ''; // opcional: si querés token, ponelo acá y envía header X-Button-Key

function fail(int $code, string $msg): void {
    http_response_code($code);
    echo json_encode(['ok' => false, 'error' => $msg], JSON_UNESCAPED_UNICODE);
    ob_end_flush(); exit;
}

function require_post_and_auth(): void {
    if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
        fail(405, 'Método no permitido (usa POST)');
    }
    $remote = $_SERVER['REMOTE_ADDR'] ?? '';
    $hdrKey = $_SERVER['HTTP_X_BUTTON_KEY'] ?? '';
    $localOk = in_array($remote, ['127.0.0.1', '::1'], true);
    $keyOk   = (BUTTON_KEY !== '' && hash_equals(BUTTON_KEY, $hdrKey));
    if (!$localOk && !$keyOk) fail(401, 'No autorizado');
}

/** Reenvía POST vacío al endpoint interno */
function forward_local(string $path): array {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $url    = $scheme . $host . $path;

    if (!function_exists('curl_init')) {
        fail(500, 'PHP-cURL no está instalado');
    }

    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS    => '',
        CURLOPT_HTTPHEADER    => ['Accept: application/json', 'Content-Length: 0'],
        CURLOPT_RETURNTRANSFER=> true,
        CURLOPT_TIMEOUT       => 5,
    ]);
    $body   = curl_exec($ch);
    $err    = curl_error($ch);
    $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($body === false) fail(502, 'Fallo reenviando: ' . $err);
    if ($status < 200 || $status >= 300) {
        fail($status ?: 502, 'Endpoint interno devolvió estado ' . $status . ' cuerpo: ' . trim((string)$body));
    }

    $data = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) return $data;
    return ['raw' => trim((string)$body)];
}

function ok(array $extra = []): void {
    echo json_encode(array_merge(['ok' => true], $extra), JSON_UNESCAPED_UNICODE);
    ob_end_flush(); exit;
}