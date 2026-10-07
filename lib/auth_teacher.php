<?php
require_once __DIR__.'/bootstrap.php';
secure_session('AGH_TEACHER', '/guru');
function teacher_user(): ?array {
    if (empty($_SESSION['teacher_id'])) return null;
    $s=db()->prepare('SELECT id,name,active FROM teachers WHERE id=? LIMIT 1');
    $s->execute([$_SESSION['teacher_id']]);
    $u=$s->fetch();
    return ($u && (int)$u['active']===1) ? $u : null;
}
function require_teacher(): array {
    $u=teacher_user(); if(!$u) redirect('/guru/'); return $u;
}
