<?php

date_default_timezone_set('Africa/Kampala');
// Define the fallback URL
$fallback_url = "http://localhost/schoolprojectt/SmartStudyProV2.3/SmartStudyProV2/";

try {
    // Path to the Cockpit bootstrap file
    $cockpit_path = __DIR__ . '/cms/bootstrap.php';

    if (file_exists($cockpit_path)) {
        require_once $cockpit_path;
    } else {
        // If the bootstrap file is missing, redirect to fallback
        header("Location: " . $fallback_url);
        exit();
    }
} catch (Exception $e) {
    // If any connection error occurs, redirect to fallback
    header("Location: " . $fallback_url);
    exit();
}

/**
 * Helper function to check if Cockpit is actually working
 * You can use this in your other files
 */
function is_cms_connected() {
    return function_exists('cockpit');
}

// 1. Load the file you just shared
require_once __DIR__ . '/cms/bootstrap.php';

// 2. Initialize the Cockpit instance
// This runs the "public static function instance" inside the class you shared
$cockpit = Cockpit::instance();

/**
 * 3. Create the missing 'cockpit' helper function manually.
 * This is the bridge that lets your index.php use cockpit('collections')
 */
if (!function_exists('cockpit')) {
    function cockpit($module = null) {
        if ($module) {
            return Cockpit::instance()->module($module);
        }
        return Cockpit::instance();
    }
}
