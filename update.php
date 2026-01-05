<?php
$db_file = __DIR__ . "/database/bookings.db";

// Make sure directory exists
if (!file_exists(dirname($db_file))) mkdir(dirname($db_file), 0777, true);

try {
    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Add the 'paid' column if it doesn't exist
    $db->exec("ALTER TABLE bookings ADD COLUMN paid INTEGER DEFAULT 0");

    echo "Column 'paid' added successfully!";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
