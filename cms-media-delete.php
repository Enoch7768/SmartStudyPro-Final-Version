<?php

require_once __DIR__ . '/auth.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid request.');
}

$name = basename((string) ($_POST['name'] ?? ''));
$path = __DIR__ . '/uploads/cms/' . $name;

if ($name === '' || !is_file($path)) {
    header('Location: /cms/media?error=missing');
    exit;
}

unlink($path);
header('Location: /cms/media?deleted=1');
exit;
