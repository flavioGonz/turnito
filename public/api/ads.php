<?php
header('Content-Type: application/json; charset=utf-8');
$base = (isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['HTTP_HOST'];
$dir = __DIR__.'/../media/ads';
$items = [];
if (is_dir($dir)){
  foreach (array_diff(scandir($dir), ['.','..']) as $f){
    $path = "$dir/$f"; if(!is_file($path)) continue;
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    $type = in_array($ext, ['mp4','webm']) ? 'video' : 'image';
    $size = filesize($path); $mtime = filemtime($path);
    $it = [
      'url' => "$base/media/ads/".rawurlencode($f),
      'type'=> $type,
      'size'=> $size,
      'mtime'=> $mtime
    ];
    if ($type==='image'){
      [$w,$h] = @getimagesize($path) ?: [null,null];
      if ($w && $h){ $it['w']=$w; $it['h']=$h; }
    }
    $items[] = $it;
  }
}
usort($items, fn($a,$b)=>$b['mtime']<=>$a['mtime']); // más recientes primero
echo json_encode(['items'=>$items,'updated_at'=>time()], JSON_UNESCAPED_SLASHES);
