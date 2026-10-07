<?php
if ((string)($_SERVER['HTTP_X_DEBUG'] ?? '') !== '1'){http_response_code(404);exit;}
require __DIR__.'/lib/bootstrap.php';
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><title>Selftest</title>
<style>body{font-family:system-ui,sans-serif;margin:24px} h2{margin-top:24px;border-bottom:1px solid #444;padding-bottom:4px} .ok{color:#0f0} .bad{color:#f55}</style></head><body>
<h1>Game Hub Selftest</h1>
<?php
$errors=[];
$checks=[
  'Config' => is_file(__DIR__.'/config/config.php') ?: 'config.php HILANG',
  'DB' => (function() use(&$errors){
    try{$pdo=db(); $pdo->query('SELECT 1'); return 'terhubung ('.cfg('db.name').')';}catch(Throwable $e){$errors[]='DB: '.$e->getMessage(); return 'GAGAL';}
  })(),
  'Storage games' => (function() use(&$errors){
    $p=__DIR__.'/storage/games'; if(!is_dir($p)){ $errors[]='storage/games tidak ada'; return 'TIDAK ADA'; }
    return 'ada';
  })(),
  'Writable' => @is_writable(__DIR__.'/storage/games') ? 'bisa tulis' : 'TIDAK BISA TULIS',
  'Base URL' => cfg('base_url'),
];
foreach($checks as $k=>$v){ echo '<h2>'.e($k).'</h2><p class="'.($errors&&str_contains($k,'DB')||$v==='GAGAL'?'bad':'ok').'">'.e($v).'</p>'; }
$s=db()->query('SELECT id,slug,status,storage_dir,title FROM games ORDER BY id DESC LIMIT 10')->fetchAll(PDO::FETCH_ASSOC);
echo '<h2>Games terakhir</h2><table border=1 cellpadding=4>';
echo '<tr><th>id</th><th>slug</th><th>status</th><th>storage_dir</th><th>title</th></tr>';
foreach($s as $g){ $p=__DIR__.'/storage/games/'.$g['storage_dir'].'/index.html'; $exists=is_file($p);
  echo '<tr><td>'.$g['id'].'</td><td>'.e($g['slug']).'</td><td>'.$g['status'].'</td><td>'.$g['storage_dir'].'</td><td>'.e($g['title']).'</td></tr>';
  echo '<tr><td colspan=5><small>file='.e($p).' exists='.($exists?'YES':'NO').'</small></td></tr>';
} echo '</table>';
if($errors){ echo '<h2>Error</h2><pre>'.implode("\n",$errors).'</pre>'; }
?>
<p><small>Buka dengan header <code>X-Debug: 1</code>. Hapus selftest.php setelah selesai.</small></p></body></html>
