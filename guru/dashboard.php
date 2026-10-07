<?php
require __DIR__.'/../lib/auth_teacher.php';$u=require_teacher();$msg=(string)($_GET['msg']??'');
$s=db()->prepare('SELECT * FROM games WHERE teacher_id=? ORDER BY created_at DESC');$s->execute([$u['id']]);$games=$s->fetchAll();
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/style.css"><title>Dashboard Guru</title></head><body><div class="wrap"><div class="topbar"><div><div class="brand">TEACHER // DASHBOARD</div><h2><?=e($u['name'])?></h2></div><div class="nav"><a href="/guru/upload.php">+ Upload Game</a><a href="/guru/logout.php">Keluar</a><button class="btn" data-theme-btn onclick="aghTheme()">Terang</button></div></div><?php if($msg):?><div class="flash ok"><?=e($msg)?></div><?php endif?><div class="card" style="padding:18px"><table class="table"><thead><tr><th>Game</th><th>Status</th><th>Dibuat</th><th>Aksi</th></tr></thead><tbody><?php if(!$games):?><tr><td colspan="4" class="empty">Belum ada game. Upload game pertama.</td></tr><?php endif?><?php foreach($games as $g):?><tr><td><strong><?=e($g['title'])?></strong><div class="small muted"><?=e($g['subject'])?> · <?=e($g['class_level'])?></div></td><td><span class="badge"><?=e($g['status'])?></span></td><td><?=e($g['created_at'])?></td><td><div class="actions"><?php if($g['status']==='published'):?><a class="btn" href="/game.php?slug=<?=urlencode($g['slug'])?>" target="_blank">Preview</a><?php endif?><form method="post" action="toggle.php"><input type="hidden" name="csrf" value="<?=e(ensure_csrf())?>"><input type="hidden" name="id" value="<?=$g['id']?>"><button class="btn mag"><?= $g['status']==='published'?'Unpublish':'Publish'?></button></form><form method="post" action="delete.php" onsubmit="return confirm('Hapus game ini?')"><input type="hidden" name="csrf" value="<?=e(ensure_csrf())?>"><input type="hidden" name="id" value="<?=$g['id']?>"><button class="btn danger">Hapus</button></form></div></td></tr><?php endforeach?></tbody></table></div>
<div class="card" style="padding:18px"><h3>Status penyimpanan</h3><div class="table">
<?php
foreach($games as $g){
 $p=__DIR__.'/../storage/games/'.$g['storage_dir'].'/index.html'; $ok=is_file($p);
 echo '<div class="row" style="margin:6px 0;font-size:13px">';
 echo '<span>'.e($g['title']).' <small class="muted">('.e($g['slug']).')</small></span>';
 echo '<span style="margin:0 8px; '.($ok?'color:var(--green)':'color:var(--red)') . '">file '.($ok?'ADA':'TIDAK ADA').'</span>';
 echo '<span style="color:var(--muted);flex:1;text-align:right">';
 echo ($ok && is_readable($p) ? 'bisa dibaca' : (is_writable($p)?'tidak punya izin tulis':''));
 echo '</span></div>';
}
?></div></div></div><script src="../assets/theme.js"></script></body></html>
