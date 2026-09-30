<?php

require_once __DIR__ . '/content-access.php';
require_login();

$user = current_user();
$bookingId = (int) ($_GET['id'] ?? 0);
$booking = paid_booking_for_resource(auth_db(), (int) $user['id'], $bookingId);

if (!$booking || empty($booking['file_path'])) {
    http_response_code(403);
    exit('Access denied.');
}

$file = private_resource_path($booking['file_path']);
if (!$file || !resource_extension_allowed($file)) {
    http_response_code(404);
    exit('Resource unavailable.');
}

$mime = resource_mime($file);
$size = filesize($file);
$start = 0;
$end = $size - 1;

if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', $_SERVER['HTTP_RANGE'], $matches)) {
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
header('Content-Disposition: inline; filename="' . rawurlencode(basename($file)) . '"');
header('Cache-Control: private, no-store, max-age=0');
header('Pragma: no-cache');
header('X-Content-Type-Options: nosniff');
header('Accept-Ranges: bytes');

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
