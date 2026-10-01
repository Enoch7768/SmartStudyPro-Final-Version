<?php

error_reporting(E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED);
ini_set('display_errors', '1');

require_once __DIR__ . '/../app/Services/SmartStudyProCms.php';

$legacyBootstrap = __DIR__ . '/../cms/bootstrap.php';
if (!is_file($legacyBootstrap)) {
    fwrite(STDERR, "Legacy CMS bootstrap was not found.\n");
    exit(1);
}

require_once $legacyBootstrap;

if (!function_exists('cockpit')) {
    fwrite(STDERR, "Legacy CMS could not be loaded.\n");
    exit(1);
}

$collections = [
    'HomePage',
    'AboutPage',
    'ContactDetails',
    'Products',
    'Courses',
    'Chapters',
    'Lessons',
    'Quizzes',
    'Pages',
    'Navigation',
    'SiteSettings',
];

$total = 0;

foreach ($collections as $collection) {
    $items = cockpit('content')->items($collection);
    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }
        SmartStudyProCms::save($collection, $item);
        $total++;
    }
    fwrite(STDOUT, $collection . ': ' . count($items) . " migrated\n");
}

fwrite(STDOUT, "Migration complete. " . $total . " documents copied into SmartStudyPro CMS.\n");
