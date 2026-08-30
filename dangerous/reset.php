<?php
require_once __DIR__ . '/../cms-init.php';

if (!is_cms_connected()) {
    die("Cockpit is not connected. Check your paths.");
}

$app = cockpit();

$user = $app->dataStorage->findOne('system/users', []);

if (!$user) {
    die("No users found in the database.");
}

$username = $user['user'];
$newPassword = 'SmartStudyPro123'; 

$user['password'] = password_hash($newPassword, PASSWORD_BCRYPT);

$app->dataStorage->save('system/users', $user);

echo "<h2>Reset Successful</h2>";
echo "Username Found: <b>{$username}</b><br>";
echo "Temporary Password: <b>{$newPassword}</b><br><br>";
echo "<span style='color:red;'><b>DELETE THIS FILE (reset.php) IMMEDIATELY!</b></span>";
