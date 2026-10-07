<?php
declare(strict_types=1);

$configFile = __DIR__ . '/../config/config.php';
if (!is_file($configFile)) {
    http_response_code(500);
    exit('Konfigurasi belum dibuat. Salin config/config.example.php menjadi config/config.php lalu isi nilainya.');
}
$config = require $configFile;

function cfg(string $key, mixed $default = null): mixed {
    global $config;
    $parts = explode('.', $key);
    $value = $config;
    foreach ($parts as $part) {
        if (!is_array($value) || !array_key_exists($part, $value)) return $default;
        $value = $value[$part];
    }
    return $value;
}

function db(): PDO {
    static $pdo;
    if ($pdo instanceof PDO) return $pdo;
    $dsn = sprintf('mysql:host=%s;dbname=%s;charset=%s', cfg('db.host'), cfg('db.name'), cfg('db.charset', 'utf8mb4'));
    $pdo = new PDO($dsn, (string)cfg('db.user'), (string)cfg('db.pass'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    return $pdo;
}

function e(string $value): string { return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function redirect(string $path): never { header('Location: ' . rtrim((string)cfg('base_url'), '/') . $path); exit; }
function app_url(string $path = ''): string { return rtrim((string)cfg('base_url'), '/') . '/' . ltrim($path, '/'); }
function now_sql(): string { return gmdate('Y-m-d H:i:s'); }
function random_code(string $prefix='GURU'): string {
    $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    $parts=[];
    for($p=0;$p<3;$p++){ $s=''; for($i=0;$i<4;$i++) $s.=$alphabet[random_int(0, strlen($alphabet)-1)]; $parts[]=$s; }
    return $prefix.'-'.implode('-', $parts);
}
function lookup_token(string $code): string {
    $normalized = strtoupper(trim($code));
    return hash_hmac('sha256', $normalized, (string)cfg('app_key'));
}
function client_ip(): string { return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0'; }

function ensure_csrf(): string {
    if (session_status() !== PHP_SESSION_ACTIVE) throw new RuntimeException('Session belum aktif');
    if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(24));
    return $_SESSION['csrf'];
}
function verify_csrf(): void {
    if (session_status() !== PHP_SESSION_ACTIVE) throw new RuntimeException('Session belum aktif');
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || empty($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $token)) {
        http_response_code(419); exit('CSRF token tidak valid. Muat ulang halaman.');
    }
}

function secure_session(string $name, string $path): void {
    if (session_status() === PHP_SESSION_ACTIVE) return;
    session_name($name);
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $path,
        'secure' => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);
    session_start();
}

function rate_limit_check(string $scope, int $limit=5, int $minutes=10): bool {
    $pdo=db();
    $cutoff = gmdate('Y-m-d H:i:s', time()-($minutes*60));
    $stmt=$pdo->prepare('SELECT COUNT(*) FROM auth_attempts WHERE scope=? AND ip_address=? AND success=0 AND created_at>=?');
    $stmt->execute([$scope, client_ip(), $cutoff]);
    return ((int)$stmt->fetchColumn()) < $limit;
}
function log_auth_attempt(string $scope, bool $success): void {
    $stmt=db()->prepare('INSERT INTO auth_attempts(scope, ip_address, success, created_at) VALUES(?,?,?,?)');
    $stmt->execute([$scope, client_ip(), $success?1:0, now_sql()]);
}

function slugify(string $title): string {
    $s = strtolower(trim($title));
    $s = preg_replace('/[^a-z0-9]+/i', '-', $s) ?? 'game';
    $s = trim($s, '-');
    if ($s==='') $s='game';
    return substr($s, 0, 60) . '-' . substr(bin2hex(random_bytes(4)),0,8);
}

function mime_for(string $file): string {
    $ext=strtolower(pathinfo($file, PATHINFO_EXTENSION));
    return [
        'html'=>'text/html; charset=UTF-8','htm'=>'text/html; charset=UTF-8','css'=>'text/css; charset=UTF-8','js'=>'application/javascript; charset=UTF-8','json'=>'application/json; charset=UTF-8',
        'png'=>'image/png','jpg'=>'image/jpeg','jpeg'=>'image/jpeg','gif'=>'image/gif','webp'=>'image/webp','svg'=>'image/svg+xml',
        'mp3'=>'audio/mpeg','wav'=>'audio/wav','ogg'=>'audio/ogg','m4a'=>'audio/mp4','mp4'=>'video/mp4','webm'=>'video/webm',
        'woff'=>'font/woff','woff2'=>'font/woff2','ttf'=>'font/ttf','txt'=>'text/plain; charset=UTF-8'
    ][$ext] ?? 'application/octet-stream';
}
