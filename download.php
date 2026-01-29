<?php
/**
 * SECURE DOWNLOAD HANDLER - SmartStudyPro
 * Serves files from Cockpit storage to the user.
 */

if (isset($_GET['file'])) {
    $filePath = $_GET['file'];

    // 1. Security: Prevent directory traversal (browsing outside the uploads folder)
    $fileName = basename($filePath);
    
    // 2. Define the path to your Cockpit uploads folder
    // Adjust this path if your folder structure differs
    $fullPath = __DIR__ . "/cms/storage/uploads/" . ltrim($filePath, '/');

    // 3. Check if file exists and is a real file
    if (file_exists($fullPath) && is_file($fullPath)) {
        
        // 4. Set headers to force download
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($fullPath));
        
        // 5. Clear buffer and read the file
        flush(); 
        readfile($fullPath);
        exit;
    } else {
        // Professional Error UI if file is missing
        echo "<div style='font-family:sans-serif; text-align:center; padding-top:50px;'>";
        echo "<h2 style='color:#e74c3c;'>Download Unavailable</h2>";
        echo "<p>The file could not be found on the server. Please contact support.</p>";
        echo "<a href='cart.php'>Return to Cart</a>";
        echo "</div>";
    }
} else {
    header("Location: cart.php");
    exit;
}