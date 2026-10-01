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
$allowedCollections = ['HomePage','AboutPage','ContactDetails','Products','Courses','Chapters','Lessons','Quizzes','Pages','Navigation','SiteSettings', ...SmartStudyProCms::collections()];
if (!in_array($collection, array_values(array_unique($allowedCollections)), true)) {
    http_response_code(422);
    exit('Invalid collection.');
}
$action = trim((string) ($_POST['action'] ?? ''));
$ids = $_POST['ids'] ?? [];

if ($collection === '' || $action !== 'delete' || !is_array($ids)) {
    http_response_code(422);
    exit('Invalid bulk action.');
}

$deleted = 0;
foreach ($ids as $id) {
    $id = trim((string) $id);
    if ($id !== '' && SmartStudyProCms::delete($collection, $id)) {
        $deleted++;
    }
}

header('Location: /cms?collection=' . urlencode($collection) . '&deleted=' . $deleted);
exit;
