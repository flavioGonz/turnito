<?php
return [
  'ADMIN_PASSWORD' => 'flavio20',
  'BASE_URL' => rtrim((isset($_SERVER['HTTPS'])?'https://':'http://').$_SERVER['HTTP_HOST'], '/'),
  'PUBLIC_ADS_PATH' => __DIR__.'/../public/media/ads',
  'STORAGE_ACTIVE' => __DIR__.'/../storage/active',
  'STORAGE_ARCHIVE' => __DIR__.'/../storage/archive',
  'MAX_UPLOAD_MB' => 200, // limita videos grandes
  'ALLOWED_EXT' => ['jpg','jpeg','png','gif','webp','mp4','webm'],
];
