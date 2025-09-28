<?php
$dsn  = 'mysql:host=127.0.0.1;dbname=turnero;charset=utf8mb4';
$user = 'root';
$pass = ''; // XAMPP por defecto

$options = [
  PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
  PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
  PDO::ATTR_EMULATE_PREPARES   => false,              // 👈 IMPORTANTE
  PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
];

$pdo = new PDO($dsn, $user, $pass, $options);
