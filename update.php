<?php
try {
    $db_file = __DIR__ . "/database/bookings.db";
    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Add created_at to bookings table
    $db->exec("ALTER TABLE bookings ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP");
    echo "Added created_at to bookings table successfully!<br>";

} catch (Exception $e) {
    echo "Note: " . $e->getMessage() . " (This might mean the column already exists).<br>";
}

try {
    // 2. Just in case, let's make sure the contact_messages table is also ready
    $db->exec("CREATE TABLE IF NOT EXISTS contact_messages (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        name TEXT,
        email TEXT,
        subject TEXT,
        message TEXT,
        status TEXT DEFAULT 'unread',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    echo "Contact table verified/created successfully!";
} catch (Exception $e) {
    echo "Error with contact table: " . $e->getMessage();
}
?>