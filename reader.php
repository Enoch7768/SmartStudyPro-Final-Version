<?php

require_once __DIR__ . '/content-access.php';
require_login();

$user = current_user();
$bookingId = (int) ($_GET['id'] ?? 0);

$db = auth_db();
ensure_bookings_columns($db);
$booking = paid_booking_for_resource($db, (int) $user['id'], $bookingId);

if (!$booking || empty($booking['file_path'])) {
    http_response_code(403);
    exit('This learning resource is not available for your account.');
}

$file = private_resource_path($booking['file_path']);
if (!$file || !resource_extension_allowed($file)) {
    http_response_code(404);
    exit('This learning resource is unavailable.');
}

$mime = resource_mime($file);
$title = $booking['service'] ?? 'Learning Resource';

if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
    header('Content-Type: ' . $mime);
    header('Content-Length: ' . filesize($file));
    exit;
}

header('X-Content-Type-Options: nosniff');
header('Cache-Control: private, no-store, max-age=0');
header('Pragma: no-cache');
header('Content-Disposition: inline; filename="' . rawurlencode(basename($file)) . '"');
header('Accept-Ranges: bytes');

$size = filesize($file);
$start = 0;
$end = $size - 1;

if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(d*)-(d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
    $start = $matches[1] === '' ? max(0, $size - (int) $matches[2]) : (int) $matches[1];
    $end = $matches[2] === '' ? $size - 1 : min($size - 1, (int) $matches[2]);
    if ($start > $end || $start >= $size) {
        http_response_code(416);
        header('Content-Range: bytes */' . $size);
        exit;
    }
    http_response_code(206);
    header("Content-Range: bytes {$start}-{$end}/{$size}");
}

header('Content-Type: ' . $mime);
header('Content-Length: ' . ($end - $start + 1));

$handle = fopen($file, 'rb');
if ($handle === false) {
    http_response_code(500);
    exit('Unable to open resource.');
}

fseek($handle, $start);
$remaining = $end - $start + 1;
while ($remaining > 0 && !feof($handle)) {
    $chunk = fread($handle, min(1024 * 1024, $remaining));
    if ($chunk === false) break;
    echo $chunk;
    $remaining -= strlen($chunk);
    flush();
}
fclose($handle);
