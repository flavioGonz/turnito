<?php
// /turnero/api/migrate.php  (DDL sin transacciones)
header('Content-Type: application/json; charset=utf-8');
require __DIR__ . '/../db.php';

$out = ['ok'=>true,'actions'=>[],'errors'=>[]];
$A = function($m) use (&$out){ $out['actions'][]=$m; };
$E = function($m) use (&$out){ $out['ok']=false; $out['errors'][]=$m; };

function tableExists(PDO $pdo,$t){$s=$pdo->prepare("SHOW TABLES LIKE ?");$s->execute([$t]);return (bool)$s->fetch();}
function colExists(PDO $pdo,$t,$c){$s=$pdo->prepare("SHOW COLUMNS FROM `$t` LIKE ?");$s->execute([$c]);return (bool)$s->fetch();}
function idxExists(PDO $pdo,$t,$i){$s=$pdo->prepare("SHOW INDEX FROM `$t` WHERE Key_name=?");$s->execute([$i]);return (bool)$s->fetch();}

try{
  /* queues */
  if(!tableExists($pdo,'queues')){
    $pdo->exec("CREATE TABLE queues(
      id INT PRIMARY KEY,
      prefix VARCHAR(5) NOT NULL DEFAULT 'C',
      pad TINYINT NOT NULL DEFAULT 3,
      current_number INT NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $A('CREATE queues');
  }else{
    if(!colExists($pdo,'queues','prefix')){ $pdo->exec("ALTER TABLE queues ADD COLUMN prefix VARCHAR(5) NOT NULL DEFAULT 'C'"); $A('ALTER queues ADD prefix'); }
    if(!colExists($pdo,'queues','pad'))   { $pdo->exec("ALTER TABLE queues ADD COLUMN pad TINYINT NOT NULL DEFAULT 3");        $A('ALTER queues ADD pad'); }
    if(!colExists($pdo,'queues','current_number')){ $pdo->exec("ALTER TABLE queues ADD COLUMN current_number INT NOT NULL DEFAULT 0"); $A('ALTER queues ADD current_number'); }
  }

  /* tickets */
  if(!tableExists($pdo,'tickets')){
    $pdo->exec("CREATE TABLE tickets(
      id INT AUTO_INCREMENT PRIMARY KEY,
      queue_id INT NOT NULL,
      number INT NOT NULL,
      status ENUM('waiting','called','served') NOT NULL DEFAULT 'waiting',
      created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
      called_at DATETIME NULL,
      served_at DATETIME NULL,
      UNIQUE KEY uq_ticket (queue_id, number),
      INDEX idx_qn (queue_id, number)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
    $A('CREATE tickets');
  }else{
    if(!colExists($pdo,'tickets','queue_id')){ $pdo->exec("ALTER TABLE tickets ADD COLUMN queue_id INT NOT NULL"); $A('ALTER tickets ADD queue_id'); }
    if(!colExists($pdo,'tickets','number'))  { $pdo->exec("ALTER TABLE tickets ADD COLUMN number INT NOT NULL");   $A('ALTER tickets ADD number'); }
    if(!colExists($pdo,'tickets','status'))  { $pdo->exec("ALTER TABLE tickets ADD COLUMN status ENUM('waiting','called','served') NOT NULL DEFAULT 'waiting'"); $A('ALTER tickets ADD status'); }
    if(!colExists($pdo,'tickets','created_at')){ $pdo->exec("ALTER TABLE tickets ADD COLUMN created_at DATETIME NULL DEFAULT CURRENT_TIMESTAMP"); $A('ALTER tickets ADD created_at'); }
    if(!colExists($pdo,'tickets','called_at')) { $pdo->exec("ALTER TABLE tickets ADD COLUMN called_at DATETIME NULL"); $A('ALTER tickets ADD called_at'); }
    if(!colExists($pdo,'tickets','served_at')) { $pdo->exec("ALTER TABLE tickets ADD COLUMN served_at DATETIME NULL"); $A('ALTER tickets ADD served_at'); }
    if(!idxExists($pdo,'tickets','uq_ticket')){ $pdo->exec("ALTER TABLE tickets ADD UNIQUE KEY uq_ticket (queue_id, number)"); $A('ALTER tickets ADD UNIQUE uq_ticket'); }
    if(!idxExists($pdo,'tickets','idx_qn'))   { $pdo->exec("ALTER TABLE tickets ADD INDEX idx_qn (queue_id, number)");         $A('ALTER tickets ADD INDEX idx_qn'); }
  }

  /* seed queue #1 */
  $s = $pdo->query("SELECT 1 FROM queues WHERE id=1");
  if(!$s->fetch()){ $pdo->exec("INSERT INTO queues (id,prefix,pad,current_number) VALUES (1,'C',3,0)"); $A('INSERT seed queue #1'); }

}catch(Throwable $e){ $E($e->getMessage()); }

echo json_encode($out, JSON_UNESCAPED_SLASHES|JSON_PRETTY_PRINT);
