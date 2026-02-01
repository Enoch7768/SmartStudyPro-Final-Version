<?php
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $subject = $_POST['subject'] ?? '';
    $message = $_POST['message'] ?? '';

    try {
        $db_file = __DIR__ . "/database/bookings.db";
        
        // Ensure directory exists
        if (!file_exists(dirname($db_file))) mkdir(dirname($db_file), 0777, true);

        $db = new PDO("sqlite:$db_file");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create contacts table if it doesn't exist
        $db->exec("CREATE TABLE IF NOT EXISTS contact_messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            email TEXT,
            subject TEXT,
            message TEXT,
            status TEXT DEFAULT 'unread',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $stmt = $db->prepare("INSERT INTO contact_messages (name, email, subject, message) 
                              VALUES (:name, :email, :subject, :message)");
        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':subject' => $subject,
            ':message' => $message
        ]);

        // Return "OK" so the template's JavaScript shows the success message
        echo "OK"; 

    } catch (Exception $e) {
        http_response_code(500);
        echo "Database Error: " . $e->getMessage();
    }
} else {
    echo "Invalid Request";
}
?>