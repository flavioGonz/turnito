<?php
// /turnero/api/marquee.php
header('Content-Type: application/json; charset=utf-8');
$file = __DIR__.'/../storage/marquee.txt';
$text = is_file($file) ? file_get_contents($file) : '';
echo json_encode(['marquee'=>$text, 'updated_at'=>time()], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES);
