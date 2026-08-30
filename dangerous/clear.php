<?php

try {
    $db_file = __DIR__ . "/database/bookings.db";
    if(!file_exists($db_file)) die("Database file does not exist.");

    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $tables = ['bookings', 'product_orders', 'messages'];

    foreach ($tables as $table) {
        $check = $db->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'")->fetch();
        
        if ($check) {
            $db->exec("DELETE FROM $table");
            
            $db->exec("DELETE FROM sqlite_sequence WHERE name='$table'");
            
            echo "Successfully cleared table: **$table** <br>";
        }
    }

    echo "<h3>Full Reset Complete!</h3>";
    echo "<a href='index.php'>Return to Site</a> | <a href='admin.php'>Go to Admin</a>";

} catch (Exception $e) {
    echo "Error resetting database: " . $e->getMessage();
}
?>