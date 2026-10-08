<?php
require __DIR__.'/../lib/auth_teacher.php';$u=require_teacher();$error='';
$allowed=['html','htm','css','js','json','png','jpg','jpeg','gif','webp','svg','mp3','wav','ogg','m4a','mp4','webm','woff','woff2','ttf','txt'];
function safe_rel(string $name): ?string { $name=str_replace('\\','/',$name); if($name===''||str_starts_with($name,'/')||str_contains($name,'../')||str_contains($name,"\0"))return null; return $name; }
if($_SERVER['REQUEST_METHOD']==='POST'){
 verify_csrf();
 $title=trim((string)($_POST['title']??''));$subject=trim((string)($_POST['subject']??''));$class=trim((string)($_POST['class_level']??''));$desc=trim((string)($_POST['description']??''));
 if($title==='')$error='Judul wajib diisi.';
 elseif(empty($_FILES['gamefile'])||$_FILES['gamefile']['error']!==UPLOAD_ERR_OK)$error='Upload gagal.';
 else {
  $tmp=$_FILES['gamefile']['tmp_name'];$orig=$_FILES['gamefile']['name'];$ext=strtolower(pathinfo($orig,PATHINFO_EXTENSION));$dir=bin2hex(random_bytes(16));$dest=__DIR__.'/../storage/games/'.$dir;$skippedExtensions=[];mkdir($dest,0750,true);
  try{
    if($ext==='html'||$ext==='htm'){
      if($_FILES['gamefile']['size']>cfg('upload.max_zip_bytes')) throw new RuntimeException('File terlalu besar.');
      if(!move_uploaded_file($tmp,$dest.'/index.html')) throw new RuntimeException('Gagal menyimpan file.');
      if(!is_readable($dest.'/index.html')) throw new RuntimeException('File tersimpan tapi tidak bisa dibaca — periksa izin folder storage/games di hosting.');
    } elseif($ext==='zip'){
      if($_FILES['gamefile']['size']>cfg('upload.max_zip_bytes')) throw new RuntimeException('ZIP terlalu besar.');
      $zip=new ZipArchive(); if($zip->open($tmp)!==true) throw new RuntimeException('ZIP tidak valid.');
      if($zip->numFiles>cfg('upload.max_files')) throw new RuntimeException('Terlalu banyak file di dalam ZIP.');
      $total=0;$filesToExtract=[];
      for($i=0;$i<$zip->numFiles;$i++){
        $st=$zip->statIndex($i);$name=safe_rel((string)$st['name']); if($name===null) throw new RuntimeException('Nama/path file tidak aman.');
        if(str_ends_with($name,'/')) continue;
        $e=strtolower(pathinfo($name,PATHINFO_EXTENSION));
        // Arsip dari project game kadang ikut membawa source server-side (mis. PHP).
        // Jangan ekstrak file tersebut, tetapi jangan gagalkan asset statis yang valid.
        if($e!=='' && !in_array($e,$allowed,true)){
          $skippedExtensions[$e]=($skippedExtensions[$e]??0)+1;
          continue;
        }
        $total+=(int)($st['size']??0); if($total>cfg('upload.max_extracted_bytes')) throw new RuntimeException('Isi ZIP terlalu besar setelah diekstrak.');
        $filesToExtract[]=['source'=>(string)$st['name'],'name'=>$name];
      }
      // ponytail: entry folder tanpa trailing slash tidak punya metadata dir di PHP build ini, jadi deteksi via path ancestor agar tidak di-extract jadi file kosong
      $dirs=[];
      foreach($filesToExtract as $entry){
        $name=$entry['name'];
        while($name!=='.'){ $name=dirname($name); if($name!=='.') $dirs[$name]=true; }
      }
      foreach($filesToExtract as $entry){
        $name=$entry['name'];if(isset($dirs[$name])) continue;
        $target=$dest.'/'.$name;$parent=dirname($target);if(!is_dir($parent))mkdir($parent,0750,true);
        $in=$zip->getStream($entry['source']);$out=fopen($target,'wb');stream_copy_to_stream($in,$out);fclose($in);fclose($out);
      }
      $zip->close();
      if(!is_file($dest.'/index.html')) throw new RuntimeException('ZIP wajib memiliki index.html di folder paling atas.');
    } else throw new RuntimeException('Hanya HTML tunggal atau ZIP yang diizinkan.');
    $slug=slugify($title);$st=db()->prepare('INSERT INTO games(teacher_id,title,slug,subject,class_level,description,storage_dir,status,created_at,updated_at) VALUES(?,?,?,?,?,?,?,?,?,?)');$st->execute([$u['id'],$title,$slug,$subject,$class,$desc,$dir,'draft',now_sql(),now_sql()]);
    $msg='Game berhasil diupload sebagai draft.';
    if(!empty($skippedExtensions)){
      $skippedCount=array_sum($skippedExtensions);$skippedTypes=implode(', ',array_map(fn($e)=>'.'.$e,array_keys($skippedExtensions)));
      $msg.=' '.$skippedCount.' file yang tidak didukung diabaikan ('.$skippedTypes.').';
    }
    redirect('/guru/dashboard.php?msg='.urlencode($msg));
  } catch(Throwable $e){
    if(is_dir($dest)){ $it=new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dest,FilesystemIterator::SKIP_DOTS),RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $f){$f->isDir()?rmdir($f->getPathname()):unlink($f->getPathname());} @rmdir($dest); }
    $error=$e->getMessage();
  }
 }
}
?>
<!doctype html><html lang="id"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><link rel="stylesheet" href="../assets/style.css"><title>Upload Game</title></head><body><div class="wrap"><div class="topbar"><div class="brand">UPLOAD // GAME</div><div class="nav"><a href="dashboard.php">← Dashboard</a><button data-theme-btn onclick="aghTheme()" aria-label="Alihkan mode terang atau gelap"></button></div></div><div class="card form-card" style="margin-top:0"><?php if($error):?><div class="flash bad"><?=e($error)?></div><?php endif?><form method="post" enctype="multipart/form-data"><input type="hidden" name="csrf" value="<?=e(ensure_csrf())?>"><div class="field"><label>Judul game</label><input name="title" required></div><div class="row"><div class="field" style="flex:1"><label>Mapel</label><input name="subject" placeholder="Informatika"></div><div class="field" style="flex:1"><label>Kelas</label><input name="class_level" placeholder="X"></div></div><div class="field"><label>Deskripsi</label><textarea name="description"></textarea></div><div class="field"><label>File game</label><input type="file" name="gamefile" accept=".html,.htm,.zip" required><div class="small muted" style="margin-top:8px">Boleh 1 file HTML mandiri, atau ZIP berisi index.html + asset lokal. Maks. 15 MB. Game yang bergantung CDN/internet belum didukung pada MVP ini.</div></div><button class="btn primary">Upload sebagai Draft</button></form></div></div><script src="../assets/theme.js"></script></body></html>
