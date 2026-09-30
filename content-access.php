<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cms-init.php';

function paid_booking_for_resource(PDO $db, int $userId, int $bookingId): ?array {
    $stmt = $db->prepare("SELECT * FROM bookings WHERE id = :id AND paid = 1 AND account_id = :account_id LIMIT 1");
    $stmt->execute([':id' => $bookingId, ':account_id' => $userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    return $row ?: null;
}

function private_resource_path(string $storedPath): ?string {
    $storedPath = trim(str_replace(chr(92), '/', $storedPath));
    $base = realpath(protected_learning_root());
    if ($base === false || $storedPath === '' || str_contains($storedPath, chr(0))) {
        return null;
    }

    $candidate = realpath($base . DIRECTORY_SEPARATOR . ltrim($storedPath, '/'));
    if ($candidate === false || !is_file($candidate)) {
        return null;
    }

    return strncmp($candidate, $base . DIRECTORY_SEPARATOR, strlen($base) + 1) === 0 ? $candidate : null;
}

function resource_mime(string $path): string {
    $mime = function_exists('mime_content_type') ? mime_content_type($path) : false;
    if (is_string($mime) && $mime !== '') return $mime;
    return match (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
        'pdf' => 'application/pdf',
        'txt' => 'text/plain',
        'md' => 'text/markdown',
        'html' => 'text/html',
        'jpg', 'jpeg' => 'image/jpeg',
        'png' => 'image/png',
        'webp' => 'image/webp',
        'mp3' => 'audio/mpeg',
        'mp4' => 'video/mp4',
        default => 'application/octet-stream',
    };
}

function resource_extension_allowed(string $path): bool {
    return in_array(strtolower(pathinfo($path, PATHINFO_EXTENSION)), [
        'pdf', 'txt', 'md', 'html', 'jpg', 'jpeg', 'png', 'webp', 'mp3', 'wav', 'm4a', 'mp4', 'webm'
    ], true);
}
