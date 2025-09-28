<?php
// /turnero/admin/index.php
// Admin dark: Publicidad (activos), Archivo, Marquesina, Logos
// Autenticación mínima (si tenés includes/auth.php, descomentá y usala)
@session_start();
// require __DIR__ . '/../includes/auth.php'; ensure_auth();

define('ADMIN_PASSWORD_DEFAULT', 'flavio20'); // si usás includes/config.php, podés ignorar esto
$cfgFile = __DIR__ . '/../includes/config.php';
if (!function_exists('admin_check')) {
  function admin_check() {
    if (!isset($_SESSION['is_admin'])) {
      if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['password'])) {
        $pwd = trim($_POST['password']);
        $expect = ADMIN_PASSWORD_DEFAULT;
        if (is_file(__DIR__.'/../includes/config.php')) {
          include __DIR__.'/../includes/config.php';
          if (defined('ADMIN_PASSWORD')) $expect = ADMIN_PASSWORD;
        }
        if ($pwd === $expect) { $_SESSION['is_admin'] = true; header('Location: index.php'); exit; }
      }
      echo '<!doctype html><meta charset="utf-8"><title>Login Admin</title>
            <style>body{display:grid;place-items:center;height:100vh;background:#0b0f14;color:#e5e7eb;font-family:system-ui}
            form{background:#0f1318;border:1px solid #1f2937;border-radius:14px;padding:20px;min-width:280px}
            input,button{width:100%;padding:10px;border-radius:10px;border:1px solid #1f2937;background:#0b0f14;color:#e5e7eb}
            button{background:#3b82f6;border:0;margin-top:10px}
            </style>
            <form method="post"><h3 style="margin:0 0 10px">Admin</h3>
            <input type="password" name="password" placeholder="Contraseña" autofocus required>
            <button>Entrar</button></form>';
      exit;
    }
  }
}
admin_check();

// Rutas y carpetas
$adsActive  = __DIR__.'/../storage/active';
$adsArchive = __DIR__.'/../storage/archive';
$adsPublic  = __DIR__.'/../public/media/ads';
$marqueeTxt = __DIR__.'/../storage/marquee.txt';

$logosDir = __DIR__.'/../storage/branding/logos';
$logosPub = __DIR__.'/../public/media/logos';
$brandCfgFile = __DIR__.'/../storage/branding/config.json';

// Asegurar carpetas
@mkdir($adsActive, 0775, true);
@mkdir($adsArchive, 0775, true);
@mkdir($adsPublic, 0775, true);
@mkdir(dirname($marqueeTxt), 0775, true);

@mkdir($logosDir, 0775, true);
@mkdir($logosPub, 0775, true);
@mkdir(dirname($brandCfgFile), 0775, true);

// Utilidades
function list_media($dir) {
  $out = [];
  if (!is_dir($dir)) return $out;
  foreach (array_diff(scandir($dir), ['.','..']) as $f) {
    $path = $dir.'/'.$f;
    if (!is_file($path)) continue;
    $ext = strtolower(pathinfo($f, PATHINFO_EXTENSION));
    $type = in_array($ext, ['mp4','webm']) ? 'video' : 'image';
    $out[] = [
      'name'=>$f, 'type'=>$type, 'path'=>$path,
      'mtime'=>filemtime($path), 'size'=>filesize($path)
    ];
  }
  usort($out, fn($a,$b)=>$b['mtime'] <=> $a['mtime']);
  return $out;
}
function h($s){ return htmlspecialchars($s, ENT_QUOTES|ENT_SUBSTITUTE, 'UTF-8'); }

// Cargar listas
$activeList  = list_media($adsActive);
$archiveList = list_media($adsArchive);

// Marquee
$marqueeVal = is_file($marqueeTxt) ? file_get_contents($marqueeTxt) : '';

// Logos
$logos = [];
if (is_dir($logosDir)) {
  foreach (array_diff(scandir($logosDir), ['.','..']) as $f) {
    if (is_file($logosDir.'/'.$f)) $logos[] = $f;
  }
  sort($logos, SORT_NATURAL|SORT_FLAG_CASE);
}
$brandCfg = ['global_logo'=>null,'per_queue'=>[]];
if (is_file($brandCfgFile)) {
  $tmp = json_decode(file_get_contents($brandCfgFile), true);
  if (is_array($tmp)) $brandCfg = array_merge($brandCfg, $tmp);
}

// Cargar colas desde DB (si existe)
$queues = [];
try {
  require_once __DIR__ . '/../db.php';
  $st = $pdo->query("SELECT id, prefix, pad FROM queues ORDER BY id ASC");
  $queues = $st->fetchAll(PDO::FETCH_ASSOC) ?: [];
} catch(Throwable $e) {
  // sin DB, pestaña Logos funciona con logo global
}

// Detectar basePath (para previsualizaciones)
$scriptDir = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/'); // /turnero/admin
$basePath  = preg_replace('#/admin$#','', $scriptDir);
if ($basePath === '/') $basePath = '';
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <title>Admin — Turnero</title>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{background:#0b0f14;color:#e5e7eb}
    .nav-tabs .nav-link{color:#93a4b8}
    .nav-tabs .nav-link.active{color:#fff;background:#0f1318;border-color:#1f2937}
    .card{background:#0f1318;border:1px solid #1f2937}
    .thumb{background:#0b0f14;border:1px solid #1f2937;border-radius:12px;display:grid;place-items:center;width:180px;height:120px;overflow:hidden}
    .thumb img,.thumb video{max-width:160px;max-height:100px}
    .badge-soft{background:#111826;border:1px solid #1f2937}
    .btn-soft{background:#111826;border:1px solid #1f2937;color:#cbd5e1}
    code{color:#9ae6b4}
  </style>
</head>
<body class="p-3">

  <div class="container-fluid">
    <h3 class="mb-3">Admin — Turnero</h3>

    <ul class="nav nav-tabs" id="tabs" role="tablist">
      <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-activos" type="button">Publicidad</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-archivo" type="button">Archivo</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-marquee" type="button">Marquesina</button></li>
      <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-logos" type="button">Logos</button></li>
    </ul>

    <div class="tab-content mt-3">
      <!-- Publicidad (activos) -->
      <div class="tab-pane fade show active" id="tab-activos">
        <div class="row g-3">
          <div class="col-lg-4">
            <div class="card p-3 h-100">
              <h5>Subir archivos</h5>
              <form class="mt-2" method="post" action="upload.php" enctype="multipart/form-data">
                <input class="form-control" type="file" name="files[]" multiple accept="image/*,video/mp4,video/webm" required>
                <small class="text-muted d-block mt-2">Se guardan en <code>/storage/active</code> y se copian a <code>/public/media/ads</code>.</small>
                <div class="d-flex gap-2 mt-3">
                  <button class="btn btn-primary">Subir</button>
                  <a class="btn btn-soft" href="sync.php">Resincronizar copias públicas</a>
                </div>
              </form>
            </div>
          </div>
          <div class="col-lg-8">
            <div class="card p-3">
              <h5 class="mb-3">Activos actuales (<?=count($activeList)?>)</h5>
              <div class="d-flex flex-wrap gap-3">
                <?php if (!$activeList): ?>
                  <div class="text-muted">No hay archivos activos.</div>
                <?php endif; ?>
                <?php foreach ($activeList as $it):
                  $u = $basePath.'/public/media/ads/'.rawurlencode($it['name']);
                ?>
                <div>
                  <div class="thumb">
                    <?php if ($it['type']==='video'): ?>
                      <video src="<?=h($u)?>" muted></video>
                    <?php else: ?>
                      <img src="<?=h($u)?>" alt="">
                    <?php endif; ?>
                  </div>
                  <div class="mt-1 small text-muted" style="max-width:180px" title="<?=h($it['name'])?>"><?=h($it['name'])?></div>
                  <div class="d-flex gap-2 mt-2">
                    <form method="post" action="action.php">
                      <input type="hidden" name="op" value="archive">
                      <input type="hidden" name="file" value="<?=h($it['name'])?>">
                      <button class="btn btn-sm btn-soft">Archivar</button>
                    </form>
                    <form method="post" action="action.php" onsubmit="return confirm('¿Eliminar definitivamente?')">
                      <input type="hidden" name="op" value="delete_active">
                      <input type="hidden" name="file" value="<?=h($it['name'])?>">
                      <button class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                  </div>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Archivo -->
      <div class="tab-pane fade" id="tab-archivo">
        <div class="card p-3">
          <h5 class="mb-3">Archivados (<?=count($archiveList)?>)</h5>
          <div class="d-flex flex-wrap gap-3">
            <?php if (!$archiveList): ?>
              <div class="text-muted">Vacío.</div>
            <?php endif; ?>
            <?php foreach ($archiveList as $it):
              $p = $adsArchive.'/'.$it['name'];
              $u = $basePath.'/public/media/ads/'.rawurlencode($it['name']); // puede no existir si se borró la copia
            ?>
            <div>
              <div class="thumb">
                <?php if ($it['type']==='video'): ?>
                  <video src="<?=h($u)?>" muted></video>
                <?php else: ?>
                  <img src="<?=h($u)?>" alt="">
                <?php endif; ?>
              </div>
              <div class="mt-1 small text-muted" style="max-width:180px" title="<?=h($it['name'])?>"><?=h($it['name'])?></div>
              <div class="d-flex gap-2 mt-2">
                <form method="post" action="action.php">
                  <input type="hidden" name="op" value="restore">
                  <input type="hidden" name="file" value="<?=h($it['name'])?>">
                  <button class="btn btn-sm btn-soft">Restaurar</button>
                </form>
                <form method="post" action="action.php" onsubmit="return confirm('¿Eliminar definitivamente del archivo?')">
                  <input type="hidden" name="op" value="delete_archive">
                  <input type="hidden" name="file" value="<?=h($it['name'])?>">
                  <button class="btn btn-sm btn-danger">Eliminar</button>
                </form>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Marquesina -->
      <div class="tab-pane fade" id="tab-marquee">
        <div class="row g-3">
          <div class="col-lg-8">
            <div class="card p-3">
              <h5>Texto de marquesina</h5>
              <form method="post" action="marquee_save.php">
                <textarea class="form-control" name="text" rows="5" placeholder="Escribí el texto que irá en la marquesina..."><?=h($marqueeVal)?></textarea>
                <div class="d-flex justify-content-end mt-3">
                  <button class="btn btn-primary">Guardar</button>
                </div>
              </form>
              <small class="text-muted">Se guarda en <code>/storage/marquee.txt</code> y la pantalla lo recarga cada ~10s.</small>
            </div>
          </div>
          <div class="col-lg-4">
            <div class="card p-3">
              <h6>API</h6>
              <div class="small">GET <code><?=h($basePath)?>/api/marquee.php</code></div>
              <div class="small">GET <code><?=h($basePath)?>/api/ads.php</code></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Logos -->
      <div class="tab-pane fade" id="tab-logos">
        <div class="row g-4">
          <div class="col-lg-5">
            <div class="card p-3 h-100">
              <h5 class="mb-2">Subir logo</h5>
              <form method="post" action="logo_upload.php" enctype="multipart/form-data">
                <input class="form-control" type="file" name="logo" accept="image/png,image/jpeg,image/webp,image/svg+xml" required>
                <small class="text-muted d-block mt-2">PNG/JPG/WebP/SVG. Se copiará también a <code>/public/media/logos</code>.</small>
                <button class="btn btn-primary mt-3">Subir</button>
              </form>
            </div>
          </div>
          <div class="col-lg-7">
            <div class="card p-3">
              <h5 class="mb-3">Seleccionar logo</h5>
              <form method="post" action="logo_save.php">
                <div class="mb-3">
                  <label class="form-label">Logo global</label>
                  <select name="global_logo" class="form-select">
                    <option value="">— sin logo —</option>
                    <?php foreach ($logos as $f): ?>
                      <option value="<?=h($f)?>" <?=$brandCfg['global_logo']===$f?'selected':''?>><?=h($f)?></option>
                    <?php endforeach; ?>
                  </select>
                  <small class="text-muted">Si una cola no tiene logo propio, se usa el global.</small>
                </div>

                <?php if ($queues): ?>
                  <hr>
                  <h6 class="mb-2">Logo por cola (opcional)</h6>
                  <?php foreach ($queues as $q): $qid=(int)$q['id']; $sel=$brandCfg['per_queue'][$qid]??''; ?>
                    <div class="row g-2 align-items-center mb-2">
                      <div class="col-4"><span class="text-muted">Cola #<?=$qid?></span></div>
                      <div class="col-8">
                        <select name="q_logo[<?=$qid?>]" class="form-select">
                          <option value="">(usar global)</option>
                          <?php foreach ($logos as $f): ?>
                            <option value="<?=h($f)?>" <?=$sel===$f?'selected':''?>><?=h($f)?></option>
                          <?php endforeach; ?>
                        </select>
                      </div>
                    </div>
                  <?php endforeach; ?>
                <?php else: ?>
                  <div class="alert alert-info">No se detectaron colas en la base de datos. Se usará el logo global.</div>
                <?php endif; ?>

                <div class="mt-3 d-flex justify-content-end">
                  <button class="btn btn-primary">Guardar logos</button>
                </div>
              </form>
            </div>

            <?php if ($logos): ?>
            <div class="card p-3 mt-3">
              <h6>Logos disponibles</h6>
              <div class="d-flex flex-wrap gap-3">
                <?php foreach ($logos as $f): $u = $basePath.'/public/media/logos/'.rawurlencode($f); ?>
                <div class="text-center">
                  <div class="thumb" style="width:200px;height:120px"><img src="<?=h($u)?>" style="max-width:180px;max-height:100px" alt=""></div>
                  <small class="d-block mt-1 text-muted text-truncate" style="max-width:200px"><?=h($f)?></small>
                </div>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>

          </div>
        </div>
      </div>

    </div>
  </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
