<?php
require __DIR__.'/lib/bootstrap.php';
if(!cfg('setup_enabled')){http_response_code(403);exit('Setup dinonaktifkan.');}
$secret=(string)($_GET['key']??'');
if(!$secret || !hash_equals((string)cfg('setup_secret'),$secret)){http_response_code(403);exit('Setup key salah.');}
$msg='';$adminCode='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $pdo=db();
  $queries=[
"CREATE TABLE IF NOT EXISTS admins (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(100) NOT NULL,code_lookup CHAR(64) NOT NULL UNIQUE,code_hash VARCHAR(255) NOT NULL,active TINYINT(1) NOT NULL DEFAULT 1,created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS teachers (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,name VARCHAR(120) NOT NULL,code_lookup CHAR(64) NOT NULL UNIQUE,code_hash VARCHAR(255) NOT NULL,active TINYINT(1) NOT NULL DEFAULT 1,created_at DATETIME NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS games (id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,teacher_id INT UNSIGNED NOT NULL,title VARCHAR(160) NOT NULL,slug VARCHAR(100) NOT NULL UNIQUE,subject VARCHAR(100) NOT NULL DEFAULT '',class_level VARCHAR(80) NOT NULL DEFAULT '',description TEXT NOT NULL,storage_dir VARCHAR(80) NOT NULL,status ENUM('draft','published') NOT NULL DEFAULT 'draft',created_at DATETIME NOT NULL,updated_at DATETIME NOT NULL,CONSTRAINT fk_game_teacher FOREIGN KEY(teacher_id) REFERENCES teachers(id) ON DELETE CASCADE) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4",
"CREATE TABLE IF NOT EXISTS auth_attempts (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,scope VARCHAR(30) NOT NULL,ip_address VARCHAR(45) NOT NULL,success TINYINT(1) NOT NULL,created_at DATETIME NOT NULL,INDEX idx_attempt(scope,ip_address,created_at)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4"
  ]; foreach($queries as $q)$pdo->exec($q);
  $count=(int)$pdo->query('SELECT COUNT(*) FROM admins')->fetchColumn();
  if($count===0){$name=trim((string)($_POST['name']??'Administrator')) ?: 'Administrator';$adminCode=random_code('ADMIN');$st=$pdo->prepare('INSERT INTO admins(name,code_lookup,code_hash,active,created_at) VALUES(?,?,?,?,?)');$st->execute([$name,lookup_token($adminCode),password_hash(strtoupper($adminCode),PASSWORD_DEFAULT),1,now_sql()]);$msg='Instalasi berhasil. Simpan kode admin di bawah ini. Setelah itu ubah setup_enabled menjadi false.';} else {$msg='Tabel sudah siap dan admin sudah ada.';}
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="assets/style.css"><title>Setup</title></head><body><div class="wrap"><div class="card form-card"><h1>Setup Ashidiq Game Hub</h1><?php if($msg):?><div class="flash ok"><?=e($msg)?></div><?php endif?><?php if($adminCode):?><div class="code-once"><?=e($adminCode)?></div><?php endif?><form method="post"><div class="field"><label>Nama administrator</label><input name="name" value="Administrator"></div><button class="btn primary">Buat database & admin</button></form><p class="small muted"><button class="btn" data-theme-btn onclick="aghTheme()">Terang</button></p></div></div><script src="assets/theme.js"></script></body></html>
