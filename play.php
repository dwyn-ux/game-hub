<?php
require __DIR__.'/lib/bootstrap.php';
$slug=(string)($_GET['slug']??'');
$file=(string)($_GET['file']??'index.html');
if(!preg_match('/^[a-z0-9-]+$/',$slug)){http_response_code(400);exit;}
$file=str_replace('\\','/',$file);
if($file==='' || str_contains($file,'..') || str_starts_with($file,'/')){http_response_code(400);exit;}
$s=db()->prepare('SELECT storage_dir,status FROM games WHERE slug=? LIMIT 1');$s->execute([$slug]);$g=$s->fetch();if(!$g || $g['status']!=='published'){http_response_code(404);exit;}
$base=realpath(__DIR__.'/storage/games/'.$g['storage_dir']);
$target=$base!==false?realpath($base.'/'.$file):null;
$exists=is_file($target??'');
if($base===false){$base=__DIR__.'/storage/games/'.$g['storage_dir'];$target=$base.'/'.$file;$exists=is_file($target);}
if(!$base||!$target||!str_starts_with($target,$base.DIRECTORY_SEPARATOR)||!$exists){
 error_log(sprintf('[game-hub] published game missing file: slug=%s storage_dir=%s file=%s base=%s target=%s exists=%d', $slug, $g['storage_dir'], $file, $base, $target, $exists));
 if(($_SESSION['teacher_id']??'')&&$base){http_response_code(404);exit('File game tidak tersimpan di disk: '.str_replace((string)cfg('base_url'), '', $target));}
 http_response_code(404);exit;}
$playSource=rtrim((string)cfg('base_url'),'/').'/play/';
// Iframe tetap memakai opaque origin. CORS diperlukan agar game dapat fetch asset
// publik miliknya (mis. questions.json) tanpa memberi akses ke domain luar.
header("Content-Security-Policy: sandbox allow-scripts allow-pointer-lock; default-src 'self' data: blob:; script-src 'self' 'unsafe-inline' 'unsafe-eval'; style-src 'self' 'unsafe-inline'; img-src 'self' data: blob:; media-src 'self' data: blob:; font-src 'self' data:; connect-src ".$playSource."; frame-src 'none'; child-src 'none'; form-action 'none'; base-uri 'none'; object-src 'none'");
header('Access-Control-Allow-Origin: *');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: no-referrer');
header('Cache-Control: public, max-age=300');
header('Content-Type: '.mime_for($target));
readfile($target);
