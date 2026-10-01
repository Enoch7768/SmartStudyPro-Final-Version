<?php

require_once __DIR__ . '/config.php';

if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => app_base_path() . '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
    ]);
    session_start();
}


function auth_db(): PDO {
    static $db = null;
    if ($db !== null) return $db;

    $dir = __DIR__ . "/database";
    if (!is_dir($dir)) mkdir($dir, 0750, true);

    $db = new PDO("sqlite:" . $dir . "/bookings.db");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $db->exec('PRAGMA busy_timeout = 5000');
    $db->exec('PRAGMA foreign_keys = ON');
    $db->exec("CREATE TABLE IF NOT EXISTS auth_login_attempts (ip TEXT PRIMARY KEY, attempts INTEGER NOT NULL DEFAULT 0, window_started INTEGER NOT NULL DEFAULT 0, blocked_until INTEGER NOT NULL DEFAULT 0)");

    $db->exec("CREATE TABLE IF NOT EXISTS users (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT NOT NULL,
        email TEXT NOT NULL UNIQUE,
        password_hash TEXT,
        google_id TEXT,
        avatar TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    return $db;
}

function ensure_bookings_columns(PDO $db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS bookings (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id TEXT,
        name TEXT,
        email TEXT,
        phone TEXT,
        date TEXT,
        service TEXT,
        message TEXT,
        price TEXT,
        paid INTEGER DEFAULT 0,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");

    $existing = array_column($db->query("PRAGMA table_info(bookings)")->fetchAll(PDO::FETCH_ASSOC), 'name');

    if (!in_array('file_path', $existing, true)) {
        $db->exec("ALTER TABLE bookings ADD COLUMN file_path TEXT");
    }
    if (!in_array('account_id', $existing, true)) {
        $db->exec("ALTER TABLE bookings ADD COLUMN account_id INTEGER");
    }
    if (!in_array('created_at', $existing, true)) {
        $db->exec("ALTER TABLE bookings ADD COLUMN created_at DATETIME");
    }
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token(?string $token): bool {
    return is_string($token) && $token !== '' && !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


function sign_video_token(string $relativePath, int $userId, int $ttlSeconds = 300): array {
    $exp = time() + $ttlSeconds;
    $sig = hash_hmac('sha256', $relativePath . '|' . $userId . '|' . $exp, app_secret());
    return ['exp' => $exp, 'sig' => $sig];
}

function verify_video_token(string $relativePath, int $userId, int $exp, string $sig): bool {
    if ($exp < time()) return false; 
    $expected = hash_hmac('sha256', $relativePath . '|' . $userId . '|' . $exp, app_secret());
    return hash_equals($expected, $sig);
}



function client_ip(): string {
    return substr((string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown'), 0, 64);
}

function login_is_blocked(): bool {
    $stmt = auth_db()->prepare('SELECT blocked_until FROM auth_login_attempts WHERE ip = :ip');
    $stmt->execute([':ip' => client_ip()]);
    return (int) $stmt->fetchColumn() > time();
}

function record_login_failure(): void {
    $db = auth_db();
    $ip = client_ip();
    $now = time();
    $stmt = $db->prepare('SELECT attempts, window_started FROM auth_login_attempts WHERE ip = :ip');
    $stmt->execute([':ip' => $ip]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $attempts = $row ? (int) $row['attempts'] : 0;
    $window = $row ? (int) $row['window_started'] : $now;
    if ($now - $window >= 900) {
        $attempts = 0;
        $window = $now;
    }
    $attempts++;
    $blocked = $attempts >= 8 ? $now + 900 : 0;
    $stmt = $db->prepare('INSERT INTO auth_login_attempts (ip, attempts, window_started, blocked_until) VALUES (:ip, :attempts, :window, :blocked) ON CONFLICT(ip) DO UPDATE SET attempts=excluded.attempts, window_started=excluded.window_started, blocked_until=excluded.blocked_until');
    $stmt->execute([':ip'=>$ip, ':attempts'=>$attempts, ':window'=>$window, ':blocked'=>$blocked]);
}

function clear_login_failures(): void {
    $stmt = auth_db()->prepare('DELETE FROM auth_login_attempts WHERE ip = :ip');
    $stmt->execute([':ip' => client_ip()]);
}

function current_user(): ?array {
    if (empty($_SESSION['user_id'])) return null;

    static $cache = null;
    if ($cache !== null) return $cache ?: null;

    $stmt = auth_db()->prepare("SELECT id, name, email, avatar, google_id FROM users WHERE id = :id");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $cache = $user ?: false;
    return $user ?: null;
}

function is_logged_in(): bool {
    return current_user() !== null;
}

function require_login(): void {
    if (!is_logged_in()) {
        $redirect = rawurlencode(safe_internal_path($_SERVER['REQUEST_URI'] ?? app_path('/profile'), app_path('/profile')));
        header('Location: ' . app_path('/login') . '?redirect=' . $redirect);
        exit;
    }
}

function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user_id'] = $user['id'];
}

function logout_user(): void {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'] ?? '', $params['secure'], $params['httponly']);
    }
    session_destroy();
}

function ensure_user_verification_columns($db) {
    try {
        $db->exec("ALTER TABLE users ADD COLUMN verification_token VARCHAR(255) NULL");
    } catch (Exception $e) {
    }

    try {
        $db->exec("ALTER TABLE users ADD COLUMN email_verified TINYINT(1) DEFAULT 0");
    } catch (Exception $e) {
    }
}
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', app_config('GOOGLE_CLIENT_ID', ''));
}