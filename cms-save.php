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
$fields = $_POST['fields'] ?? [];

if ($collection === '' || !is_array($fields)) {
    http_response_code(422);
    exit('Invalid content.');
}

$data = [];
foreach ($fields as $key => $value) {
    $key = trim((string) $key);
    if ($key === '' || $key === '_id') {
        continue;
    }
    $value = is_string($value) ? trim($value) : $value;
    if (is_string($value) && (($value[0] ?? '') === '{' || ($value[0] ?? '') === '[')) {
        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            $value = $decoded;
        }
    }
    $data[$key] = $value;
}

if ($id !== '') {
    $data['_id'] = $id;
}

SmartStudyProCms::save($collection, $data);
header('Location: cms.php?collection=' . urlencode($collection) . '&saved=1');
exit;
