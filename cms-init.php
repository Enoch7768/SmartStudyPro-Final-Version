<?php

date_default_timezone_set('Africa/Kampala');

require_once __DIR__ . '/app/Services/SmartStudyProCms.php';

function is_cms_connected(): bool {
    try {
        SmartStudyProCms::db()->query('SELECT 1');
        return true;
    } catch (Throwable $e) {
        return false;
    }
}
