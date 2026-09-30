<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('CLI only.');
}

require_once __DIR__ . '/../config.php';

$sourceRoot = realpath(__DIR__ . '/../cms/storage/uploads');
$targetRoot = protected_learning_root();

if ($sourceRoot === false || !is_dir($sourceRoot)) {
    fwrite(STDERR, "Cockpit upload storage was not found.\n");
    exit(1);
}

if (!is_dir($targetRoot) && !mkdir($targetRoot, 0750, true)) {
    fwrite(STDERR, "Private learning storage could not be created.\n");
    exit(1);
}

$dbPath = __DIR__ . '/../database/bookings.db';
if (!is_file($dbPath)) {
    fwrite(STDERR, "Bookings database was not found.\n");
    exit(1);
}

$db = new PDO('sqlite:' . $dbPath);
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$rows = $db->query("SELECT id, file_path FROM bookings WHERE file_path IS NOT NULL AND file_path <> ''")->fetchAll(PDO::FETCH_ASSOC);
$migrated = 0;
$skipped = 0;
$failed = 0;

foreach ($rows as $row) {
    $storedPath = trim(str_replace(chr(92), '/', (string) $row['file_path']));
    $relative = ltrim($storedPath, '/');

    if ($relative === '' || str_contains($relative, chr(0))) {
        $skipped++;
        continue;
    }

    $source = realpath($sourceRoot . DIRECTORY_SEPARATOR . $relative);
    if ($source === false || !is_file($source) || strncmp($source, $sourceRoot . DIRECTORY_SEPARATOR, strlen($sourceRoot) + 1) !== 0) {
        $skipped++;
        continue;
    }

    $destination = $targetRoot . DIRECTORY_SEPARATOR . $relative;
    $destinationDirectory = dirname($destination);

    if (!is_dir($destinationDirectory) && !mkdir($destinationDirectory, 0750, true)) {
        $failed++;
        continue;
    }

    if (!copy($source, $destination)) {
        $failed++;
        continue;
    }

    chmod($destination, 0640);

    $update = $db->prepare("UPDATE bookings SET file_path = :file_path WHERE id = :id");
    $update->execute([
        ':file_path' => $relative,
        ':id' => (int) $row['id']
    ]);

    $migrated++;
}

fwrite(STDOUT, "Migrated: {$migrated}\n");
fwrite(STDOUT, "Skipped: {$skipped}\n");
fwrite(STDOUT, "Failed: {$failed}\n");

exit($failed > 0 ? 1 : 0);
