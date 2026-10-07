<?php
require __DIR__.'/lib/bootstrap.php';
$q=trim((string)($_GET['q'] ?? ''));
$subject=trim((string)($_GET['subject'] ?? ''));
$sql='SELECT g.*,t.name teacher_name FROM games g JOIN teachers t ON t.id=g.teacher_id WHERE g.status="published"';
$params=[];
if($q!==''){ $sql.=' AND (g.title LIKE ? OR g.description LIKE ?)'; $params[]="%$q%"; $params[]="%$q%"; }
if($subject!==''){ $sql.=' AND g.subject=?'; $params[]=$subject; }
$sql.=' ORDER BY g.created_at DESC';
$s=db()->prepare($sql);$s->execute($params);$games=$s->fetchAll();
$subjects=db()->query('SELECT DISTINCT subject FROM games WHERE status="published" AND subject<>"" ORDER BY subject')->fetchAll(PDO::FETCH_COLUMN);
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?=e(cfg('app_name'))?></title><link rel="stylesheet" href="assets/style.css"></head><body><div class="wrap">
<div class="topbar"><div class="brand">ASHIDIQ // GAME HUB</div><div class="nav"><a href="/">Game</a><a href="/guru/">Akses Guru</a><button data-theme-btn onclick="aghTheme()" aria-label="Alihkan mode terang atau gelap"></button></div></div>
<section class="hero"><span class="badge">LEARNING GAME PLATFORM</span><h1>Belajar lewat game, bukan cuma lembar soal.</h1><p class="muted">Kumpulan game pembelajaran yang diunggah guru. Pilih game, buka, lalu mainkan langsung di browser.</p>
<form method="get" class="row" style="margin-top:18px"><input name="q" value="<?=e($q)?>" placeholder="Cari game..." style="flex:1;min-width:220px;padding:12px;border-radius:12px;border:1px solid var(--border);background:var(--panel2);color:var(--text)"><select name="subject" style="padding:12px;border-radius:12px;border:1px solid var(--border);background:var(--panel2);color:var(--text)"><option value="">Semua mapel</option><?php foreach($subjects as $s):?><option <?= $subject===$s?'selected':''?>><?=e($s)?></option><?php endforeach?></select><button class="btn primary">Cari</button></form></section>
<div style="height:18px"></div><section class="grid">
<?php if(!$games):?><div class="card empty">Belum ada game yang cocok.</div><?php endif?>
<?php foreach($games as $g): $h=abs(crc32(strtolower($g['subject'] ?: 'umum')))%360; ?><article class="card game" style="--h:<?=$h?>"><span class="badge subj" style="--h:<?=$h?>"><?=e($g['subject'] ?: 'Umum')?> · <?=e($g['class_level'] ?: 'Semua kelas')?></span><h3><?=e($g['title'])?></h3><p class="muted small"><?=e($g['description'])?></p><p class="small muted">Guru: <?=e($g['teacher_name'])?></p><a class="btn primary" href="game.php?slug=<?=urlencode($g['slug'])?>">Mainkan</a></article><?php endforeach?>
</section></div><script src="assets/theme.js"></script></body></html>
