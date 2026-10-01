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

if (!class_exists('Cockpit')) {
    fwrite(STDERR, "Legacy CMS bootstrap loaded, but the Cockpit class is unavailable.\n");
    exit(1);
}

try {
    $legacyApp = Cockpit::instance(__DIR__ . '/../cms');
    $content = $legacyApp->module('content');
} catch (Throwable $e) {
    fwrite(STDERR, "Legacy CMS could not be initialized: " . $e->getMessage() . "\n");
    exit(1);
}

if (!$content) {
    fwrite(STDERR, "Legacy CMS content module could not be loaded.\n");
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
    try {
        $items = $content->items($collection);
    } catch (Throwable $e) {
        fwrite(STDERR, $collection . ': failed to read legacy data: ' . $e->getMessage() . "\n");
        continue;
    }

    if (!is_array($items)) {
        $items = [];
    }

    $migrated = 0;

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        SmartStudyProCms::save($collection, $item);
        $migrated++;
        $total++;
    }

    fwrite(STDOUT, $collection . ': ' . $migrated . " migrated\n");
}

fwrite(STDOUT, "Migration complete. " . $total . " documents copied into SmartStudyPro CMS.\n");
