<?php
require __DIR__.'/../lib/auth_teacher.php';
if(teacher_user()) redirect('/guru/dashboard.php');
$error='';
if($_SERVER['REQUEST_METHOD']==='POST'){
 if(!rate_limit_check('teacher')){$error='Terlalu banyak percobaan. Coba lagi beberapa menit.';} else {
  $code=strtoupper(trim((string)($_POST['code']??'')));$lookup=lookup_token($code);$s=db()->prepare('SELECT * FROM teachers WHERE code_lookup=? AND active=1 LIMIT 1');$s->execute([$lookup]);$u=$s->fetch();$ok=$u && password_verify($code,$u['code_hash']);log_auth_attempt('teacher',(bool)$ok);
  if($ok){session_regenerate_id(true);$_SESSION['teacher_id']=$u['id'];redirect('/guru/dashboard.php');} else $error='Kode guru tidak valid.';
 }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/style.css"><title>Akses Guru</title></head><body><div class="wrap"><div class="card form-card"><div class="brand">TEACHER // ACCESS</div><h1>Masukkan kode guru</h1><p class="muted">Tidak perlu username atau password.</p><?php if($error):?><div class="flash bad"><?=e($error)?></div><?php endif?><form method="post"><div class="field"><label>Kode akses</label><input name="code" autocomplete="one-time-code" required placeholder="GURU-XXXX-XXXX-XXXX"></div><button class="btn primary">Masuk</button></form><p class="small muted"><a href="/" style="color:var(--cyan)">← Kembali ke portal</a></p></div></div><button class="theme-float" data-theme-btn onclick="aghTheme()" aria-label="Alihkan mode terang atau gelap"></button><script src="../assets/theme.js"></script></body></html>
