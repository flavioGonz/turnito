<?php
// /turnero/admin/logo_upload.php
@session_start();
// require __DIR__.'/../includes/auth.php'; ensure_auth();

$logosDir = __DIR__.'/../storage/branding/logos';
$logosPub = __DIR__.'/../public/media/logos';
@mkdir($logosDir,0775,true);
@mkdir($logosPub,0775,true);

if (!isset($_FILES['logo']) || $_FILES['logo']['error'] !== UPLOAD_ERR_OK) {
  header('Location: index.php#tab-logos'); exit;
}

$orig = $_FILES['logo']['name'];
$ext  = strtolower(pathinfo($orig, PATHINFO_EXTENSION));
$ok   = in_array($ext, ['png','jpg','jpeg','webp','svg']);
if (!$ok) { header('Location: index.php#tab-logos'); exit; }

$base = preg_replace('/[^A-Za-z0-9._-]/','_', pathinfo($orig, PATHINFO_FILENAME));
$name = $base.'.'.$ext;
$dst1 = $logosDir.'/'.$name;
$dst2 = $logosPub.'/'.$name;

if (move_uploaded_file($_FILES['logo']['tmp_name'], $dst1)) {
  @copy($dst1, $dst2);
}
header('Location: index.php#tab-logos');
