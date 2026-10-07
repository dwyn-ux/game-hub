<?php
require __DIR__.'/lib/bootstrap.php';
$slug=(string)($_GET['slug']??'');
$s=db()->prepare('SELECT g.*,t.name teacher_name FROM games g JOIN teachers t ON t.id=g.teacher_id WHERE g.slug=? AND g.status="published" LIMIT 1');$s->execute([$slug]);$g=$s->fetch();if(!$g){http_response_code(404);exit('Game tidak ditemukan.');}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e($g['title'])?> - <?=e(cfg('app_name'))?></title><link rel="stylesheet" href="assets/style.css"></head><body><div class="wrap"><div class="topbar"><div><div class="brand">ASHIDIQ // GAME HUB</div><h2 style="margin:8px 0 0"><?=e($g['title'])?></h2><div class="muted small"><?=e($g['subject'])?> · <?=e($g['class_level'])?> · <?=e($g['teacher_name'])?></div></div><div class="nav"><a href="/">← Kembali</a></div></div><iframe class="game-frame" sandbox="allow-scripts allow-pointer-lock" referrerpolicy="no-referrer" src="<?=e(app_url('play/'.$g['slug'].'/index.html'))?>"></iframe></div></body></html>
