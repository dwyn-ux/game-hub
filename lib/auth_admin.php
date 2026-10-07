<?php
require_once __DIR__.'/bootstrap.php';
secure_session('AGH_ADMIN', '/admin');
function admin_user(): ?array {
    if (empty($_SESSION['admin_id'])) return null;
    $s=db()->prepare('SELECT id,name,active FROM admins WHERE id=? LIMIT 1');
    $s->execute([$_SESSION['admin_id']]);
    $u=$s->fetch();
    return ($u && (int)$u['active']===1) ? $u : null;
}
function require_admin(): array { $u=admin_user(); if(!$u) redirect('/admin/'); return $u; }
