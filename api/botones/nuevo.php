<?php
declare(strict_types=1);
require __DIR__ . '/common.php';

// Enforce POST + local auth (see common.php BUTTON_KEY or loopback)
require_post_and_auth();

// Forward request to the internal API that creates a new turno
$data = forward_local('/turnero/api/botones/nuevoTurno.php');

if (is_array($data) && isset($data['numero'])) {
    ok(['action' => 'nuevo', 'numero' => $data['numero']]);
}

ok(['action' => 'nuevo', 'payload' => $data]);
