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
    $storage = $legacyApp->dataStorage;
} catch (Throwable $e) {
    fwrite(STDERR, "Legacy CMS could not be initialized: " . $e->getMessage() . "\n");
    exit(1);
}

if (!$storage) {
    fwrite(STDERR, "Legacy CMS data storage could not be initialized.\n");
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

$singletons = [
    'HomePage',
    'AboutPage',
    'ContactDetails',
];

$total = 0;

foreach ($collections as $collection) {
    $modelPath = __DIR__ . '/../cms/storage/content/' . $collection . '.model.php';

    if (!is_file($modelPath)) {
        fwrite(STDOUT, $collection . ': skipped (legacy model not found)\n');
        continue;
    }

    try {
        if (in_array($collection, $singletons, true)) {
            $item = $storage->findOne('content/singletons', ['_model' => $collection]);
            $items = $item ? [$item] : [];
        } else {
            $items = $storage->find('content/collections/' . $collection)->toArray();
        }
    } catch (Throwable $e) {
        fwrite(STDERR, $collection . ': failed to read legacy data: ' . $e->getMessage() . "\n");
        continue;
    }

    $migrated = 0;

    foreach ($items as $item) {
        if (!is_array($item)) {
            continue;
        }

        if (in_array($collection, $singletons, true) && !isset($item['_model'])) {
            $item['_model'] = $collection;
        }

        SmartStudyProCms::save($collection, $item);
        $migrated++;
        $total++;
    }

    fwrite(STDOUT, $collection . ': ' . $migrated . " migrated\n");
}

fwrite(STDOUT, "Migration complete. " . $total . " documents copied into SmartStudyPro CMS.\n");
