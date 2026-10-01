<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/app/Services/SmartStudyProCms.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !verify_csrf_token($_POST['csrf_token'] ?? null)) {
    http_response_code(403);
    exit('Invalid request.');
}

$collection = trim((string) ($_POST['collection'] ?? ''));
$id = trim((string) ($_POST['id'] ?? ''));

if ($collection === '' || $id === '') {
    http_response_code(422);
    exit('Invalid content.');
}

SmartStudyProCms::delete($collection, $id);
header('Location: cms.php?collection=' . urlencode($collection) . '&deleted=1');
exit;
