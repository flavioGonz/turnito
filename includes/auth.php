<?php
session_start();
$config = require __DIR__.'/config.php';

function ensure_auth() {
  if (!isset($_SESSION['ok'])) {
    if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['password'])) {
      $cfg = require __DIR__.'/config.php';
      if (hash_equals($cfg['ADMIN_PASSWORD'], $_POST['password'])) {
        $_SESSION['ok'] = true;
        header('Location: index.php');
        exit;
      }
    }
    echo '<!doctype html><meta charset="utf-8"><title>Login</title>
    <style>body{font-family:system-ui;display:grid;place-items:center;height:100vh}form{padding:24px;border:1px solid #ddd;border-radius:12px;min-width:300px}input[type=password],button{width:100%;padding:10px;margin-top:8px}</style>
    <form method=post><h3>Acceso admin</h3><input type=password name=password placeholder="Password" autofocus><button>Entrar</button></form>';
    exit;
  }
}
