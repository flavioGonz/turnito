<?php
// admin/index.php (dark + galería + editor de marquee)
require __DIR__.'/../includes/auth.php'; ensure_auth();
$cfg = require __DIR__.'/../includes/config.php';
require __DIR__.'/../includes/helpers.php';
ensure_dirs($cfg);

function list_files_by_mtime(string $dir): array {
  if (!is_dir($dir)) return [];
  $items = [];
  foreach (array_diff(scandir($dir), ['.','..']) as $f) {
    $p = rtrim($dir, '/').'/'.$f;
    if (is_file($p)) $items[] = ['name'=>$f, 'mtime'=>filemtime($p)];
  }
  usort($items, fn($a,$b)=>$b['mtime'] <=> $a['mtime']); // recientes primero
  return $items;
}

$active  = list_files_by_mtime($cfg['STORAGE_ACTIVE']);
$arch    = list_files_by_mtime($cfg['STORAGE_ARCHIVE']);

// Marquee actual (archivo plano)
$marqueeFile = __DIR__.'/../storage/marquee.txt';
$marqueeText = is_file($marqueeFile) ? file_get_contents($marqueeFile) : '';
?>
<!doctype html>
<html lang="es">
<meta charset="utf-8">
<title>Admin Turnero</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
  :root { --gap: 16px; }
  html,body{background:#0b0d11;color:#e5e7eb}
  .navbar, .nav-tabs .nav-link.active{ background:#111418; color:#e5e7eb; }
  .nav-tabs .nav-link{ color:#9aa3af; border:none; }
  .nav-tabs .nav-link.active{ border-bottom:2px solid #3b82f6; }
  .btn, .form-control, .form-select, .card { border-radius:14px; }
  .btn-primary{ background:#3b82f6; border-color:#3b82f6; }
  .btn-outline-secondary{ color:#cbd5e1; border-color:#334155; }
  .btn-outline-danger{ color:#fca5a5; border-color:#7f1d1d; }
  .btn-outline-success{ color:#86efac; border-color:#14532d; }
  .drop{border:2px dashed #334155;border-radius:16px;padding:24px;text-align:center;background:#0f1318}
  .grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:var(--gap)}
  .card{background:#0f1318;border:1px solid #1f2937}
  .card img,.card video{width:100%;height:160px;object-fit:cover;border-radius:12px}
  .filename{max-width:70%;display:inline-block;vertical-align:middle;color:#cbd5e1}
  .text-muted{color:#9aa3af !important}
  .form-control, .form-select, textarea{background:#0b0f14;color:#e5e7eb;border-color:#1f2937}
  .alert-info{background:#0f172a;color:#cbd5e1;border-color:#1f2937}
  .shadow-soft{box-shadow:0 10px 30px rgba(0,0,0,.35)}
</style>

<nav class="navbar px-3 mb-3 shadow-soft">
  <div class="container-fluid">
    <span class="navbar-brand mb-0 h1 text-light">Panel — Turnero</span>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="sync.php" title="Copiar activos a /public/media/ads si falta">Resincronizar</a>
    </div>
  </div>
</nav>

<div class="container pb-5">
  <ul class="nav nav-tabs" role="tablist">
    <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-ads" type="button">Publicidad</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-archive" type="button">Archivo (<?=count($arch)?>)</button></li>
    <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-marquee" type="button">Marquesina</button></li>
  </ul>

  <div class="tab-content pt-4">

    <!-- Pestaña Publicidad -->
    <div class="tab-pane fade show active" id="tab-ads">
      <div class="row g-4">
        <!-- Subida -->
        <div class="col-lg-5">
          <div class="drop" id="drop">
            <h5 class="mb-1">Subir imágenes o videos</h5>
            <p class="text-muted">Arrastrá y soltá o elegí archivos</p>
            <form id="up" method="post" action="upload.php" enctype="multipart/form-data">
              <input class="form-control" type="file" name="files[]" multiple accept="image/*,video/mp4,video/webm">
              <button class="btn btn-primary mt-3">Subir</button>
            </form>
            <small class="text-muted d-block mt-2">Permitidos: jpg, jpeg, png, webp, gif, mp4, webm</small>
          </div>
        </div>

        <!-- Galería activos -->
        <div class="col-lg-7">
          <h6 class="text-muted mb-2">Activos (<?=count($active)?>)</h6>
          <?php if (empty($active)): ?>
            <div class="alert alert-info">No hay archivos activos. Subí alguno o restaurá desde Archivo.</div>
          <?php else: ?>
          <div class="grid">
            <?php foreach ($active as $it):
              $f = $it['name'];
              $url = '../public/media/ads/'.rawurlencode($f); // relativo desde /admin/
              $isVideo = preg_match('/\.(mp4|webm)$/i', $f);
            ?>
              <div class="card p-2">
                <?php if ($isVideo): ?>
                  <video src="<?=htmlspecialchars($url)?>" muted playsinline preload="metadata"></video>
                <?php else: ?>
                  <img src="<?=htmlspecialchars($url)?>" alt="">
                <?php endif; ?>
                <div class="d-flex justify-content-between align-items-center mt-2">
                  <small class="text-truncate filename" title="<?=htmlspecialchars($f)?>"><?=htmlspecialchars($f)?></small>
                  <div class="btn-group">
                    <form method="post" action="action.php">
                      <input type="hidden" name="op" value="archive">
                      <input type="hidden" name="file" value="<?=htmlspecialchars($f)?>">
                      <button class="btn btn-sm btn-outline-secondary">Archivar</button>
                    </form>
                    <form method="post" action="action.php" onsubmit="return confirm('¿Eliminar definitivamente?');">
                      <input type="hidden" name="op" value="delete">
                      <input type="hidden" name="scope" value="active">
                      <input type="hidden" name="file" value="<?=htmlspecialchars($f)?>">
                      <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                    </form>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <!-- Pestaña Archivo -->
    <div class="tab-pane fade" id="tab-archive">
      <?php if (empty($arch)): ?>
        <div class="alert alert-info">No hay elementos en el archivo.</div>
      <?php else: ?>
      <div class="grid">
        <?php foreach ($arch as $it):
          $f = $it['name'];
        ?>
          <div class="card p-2">
            <div class="ratio ratio-16x9 d-flex align-items-center justify-content-center rounded" style="background:#0b0f14;border:1px dashed #334155">
              <span class="text-muted">Archivado</span>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-2">
              <small class="text-truncate filename" title="<?=htmlspecialchars($f)?>"><?=htmlspecialchars($f)?></small>
              <div class="btn-group">
                <form method="post" action="action.php">
                  <input type="hidden" name="op" value="restore">
                  <input type="hidden" name="file" value="<?=htmlspecialchars($f)?>">
                  <button class="btn btn-sm btn-outline-success">Restaurar</button>
                </form>
                <form method="post" action="action.php" onsubmit="return confirm('¿Eliminar definitivamente?');">
                  <input type="hidden" name="op" value="delete">
                  <input type="hidden" name="scope" value="archive">
                  <input type="hidden" name="file" value="<?=htmlspecialchars($f)?>">
                  <button class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <!-- Pestaña Marquesina -->
    <div class="tab-pane fade" id="tab-marquee">
      <div class="row">
        <div class="col-lg-8">
          <form method="post" action="marquee_save.php" class="card p-3">
            <h5 class="mb-3">Texto de la Marquesina</h5>
            <textarea name="text" class="form-control" rows="6" placeholder="Escribí el mensaje para la marquesina..."><?=htmlspecialchars($marqueeText)?></textarea>
            <div class="d-flex justify-content-between align-items-center mt-3">
              <small class="text-muted">Se guarda en <code>storage/marquee.txt</code></small>
              <button class="btn btn-primary">Guardar</button>
            </div>
          </form>
        </div>
        <div class="col-lg-4">
          <div class="card p-3">
            <h6 class="mb-2">Vista previa rápida</h6>
            <div style="white-space:nowrap;overflow:hidden;border:1px dashed #334155;border-radius:12px;padding:10px;background:#0b0f14">
              <div id="mq" style="display:inline-block;padding-left:100%;animation:scroll 15s linear infinite"><?=htmlspecialchars($marqueeText)?></div>
            </div>
            <style>@keyframes scroll{0%{transform:translateX(0)}100%{transform:translateX(-100%)}}</style>
            <small class="text-muted d-block mt-2">La pantalla real usará el JSON de <code>/turnero/api/marquee.php</code>.</small>
          </div>
        </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Drag & Drop
const drop = document.getElementById('drop');
const form = document.getElementById('up');
if (drop && form){
  ['dragover','dragenter'].forEach(e=>drop.addEventListener(e,ev=>{ev.preventDefault();drop.style.background='#0b0f14'}));
  ['dragleave','drop'].forEach(e=>drop.addEventListener(e,ev=>{ev.preventDefault();drop.style.background='#0f1318'}));
  drop.addEventListener('drop',ev=>{
    ev.preventDefault();
    const dt=ev.dataTransfer; const input=form.querySelector('input[type=file]');
    input.files=dt.files; form.submit();
  });
}
</script>
