<?php

http_response_code(403);
header('Content-Type: text/html; charset=UTF-8');
header('Cache-Control: no-store, private');
header('X-Content-Type-Options: nosniff');

$location = 'error.php?code=403';

if (!headers_sent()) {
    header('Location: ' . $location, true, 302);
    exit;
}

echo 'Access denied.';
