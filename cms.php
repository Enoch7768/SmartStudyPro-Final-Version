<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/app/Services/SmartStudyProCms.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

$collections = SmartStudyProCms::collections();
$known = ['HomePage','AboutPage','ContactDetails','Products','Courses','Chapters','Lessons','Quizzes','Pages','Navigation','SiteSettings'];
$collections = array_values(array_unique(array_merge($known, $collections)));
$collection = trim((string) ($_GET['collection'] ?? 'HomePage'));
if (!in_array($collection, $collections, true)) {
    $collection = 'HomePage';
}

$documents = SmartStudyProCms::items($collection);
$editingId = trim((string) ($_GET['id'] ?? ''));
$editing = $editingId !== '' ? SmartStudyProCms::item($collection, ['_id' => $editingId]) : null;

$fields = [];
$source = $editing ?? ($documents[0] ?? []);
foreach ($source as $key => $value) {
    if ($key !== '_id') {
        $fields[$key] = $value;
    }
}
if (!$fields) {
    $fields = cms_template($collection);
}

function cms_template(string $collection): array {
    return match ($collection) {
        'HomePage' => ['Title' => '', 'SEO-Title' => '', 'SEO-Description' => '', 'Content' => ''],
        'AboutPage' => ['Title' => '', 'SEO-Title' => '', 'SEO-Description' => '', 'Content' => ''],
        'ContactDetails' => ['phone' => '', 'email' => '', 'address' => ''],
        'Products' => ['Title' => '', 'Category' => '', 'Price' => '', 'Description' => '', 'ProductType' => '', 'Image' => '', 'ProductFile' => '', 'SEO-Title' => '', 'SEO-Description' => ''],
        'Courses' => ['Title' => '', 'Subtitle' => '', 'Category' => '', 'Price' => '', 'Description' => '', 'Image' => '', 'SEO-Title' => '', 'SEO-Description' => ''],
        'Chapters' => ['Title' => '', 'COURSE TITLE' => '', 'CHAPTER ID' => '', 'Order' => ''],
        'Lessons' => ['Title' => '', 'Chapter' => '', 'Order' => '', 'VIDEO FILE' => '', 'Content' => ''],
        'Quizzes' => ['Lesson' => '', 'Question' => '', 'Options' => '', 'Correct Answer' => ''],
        'Pages' => ['Title' => '', 'Slug' => '', 'Content' => '', 'SEO-Title' => '', 'SEO-Description' => ''],
        'Navigation' => ['Title' => '', 'Items' => ''],
        'SiteSettings' => ['Title' => '', 'Value' => ''],
        default => ['Title' => '', 'Content' => ''],
    };
}

function cms_value(mixed $value): string {
    if (is_array($value) || is_object($value)) {
        return json_encode($value, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
    return (string) $value;
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SmartStudyPro CMS</title>
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/main.css" rel="stylesheet">
<link href="assets/css/responsive.css" rel="stylesheet">
<style>
body{background:#f5f7fb}.cms-shell{min-height:100vh}.cms-sidebar{width:260px;background:#0c086b;color:#fff}.cms-brand{font-weight:800}.cms-nav a{color:rgba(255,255,255,.78);text-decoration:none;border-radius:12px;padding:10px 12px;display:block}.cms-nav a:hover,.cms-nav a.active{background:rgba(255,255,255,.12);color:#fff}.cms-main{min-width:0}.cms-card{border:0;border-radius:20px;box-shadow:0 12px 40px rgba(12,8,107,.08)}.cms-table td,.cms-table th{vertical-align:middle}.field-card{border:1px solid #e8ebf2;border-radius:16px}.json-field{min-height:180px;font-family:ui-monospace,monospace}.cms-sidebar{flex-shrink:0}@media(max-width:900px){.cms-sidebar{width:82px}.cms-sidebar .label,.cms-sidebar .cms-brand span{display:none}.cms-sidebar .cms-nav a{text-align:center}.cms-sidebar .cms-nav i{margin:0!important}}
</style>
</head>
<body>
<div class="cms-shell d-flex">
<aside class="cms-sidebar p-3">
<div class="cms-brand fs-5 mb-4"><i class="bi bi-mortarboard-fill me-2"></i><span>SmartStudyPro CMS</span></div>
<div class="small text-white-50 mb-2 label">CONTENT</div>
<nav class="cms-nav d-grid gap-1">
<?php foreach ($known as $name): ?>
<a class="<?= $collection === $name ? 'active' : '' ?>" href="/cms?collection=<?= urlencode($name) ?>"><i class="bi bi-grid me-2"></i><span class="label"><?= htmlspecialchars($name) ?></span></a>
<?php endforeach; ?>
</nav>
<div class="small text-white-50 mt-4 mb-2 label">SYSTEM</div>
<a class="cms-nav text-white text-decoration-none" href="cms-media.php"><i class="bi bi-images me-2"></i><span class="label">Media Library</span></a>
<a class="cms-nav text-white text-decoration-none" href="admin.php"><i class="bi bi-arrow-left me-2"></i><span class="label">Admin Dashboard</span></a>
</aside>
<main class="cms-main flex-grow-1 p-3 p-lg-5">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
<div><div class="text-muted small">SmartStudyPro content management</div><h1 class="h3 fw-bold mb-0"><?= htmlspecialchars($collection) ?></h1></div>
<a href="/cms?collection=<?= urlencode($collection) ?>&new=1" class="btn btn-primary rounded-pill px-4"><i class="bi bi-plus-lg me-1"></i>New content</a>
</div>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-success rounded-4 border-0">Content saved successfully.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success rounded-4 border-0">Content deleted successfully.</div><?php endif; ?>
<div class="card cms-card mb-4"><div class="card-body p-0"><div class="table-responsive"><table class="table cms-table mb-0"><thead><tr><th class="px-4">ID</th><th>Title / Name</th><th>Preview</th><th class="text-end px-4">Actions</th></tr></thead><tbody>
<?php foreach ($documents as $doc): $title = $doc['Title'] ?? $doc['title'] ?? $doc['Name'] ?? $doc['name'] ?? $doc['_id']; $preview = $doc['Content'] ?? $doc['Description'] ?? $doc['content'] ?? ''; ?>
<tr><td class="px-4 small text-muted"><?= htmlspecialchars((string)$doc['_id']) ?></td><td class="fw-semibold"><?= htmlspecialchars((string)$title) ?></td><td class="text-muted"><?= htmlspecialchars(mb_strimwidth(strip_tags(cms_value($preview)),0,100,'…')) ?></td><td class="text-end px-4"><a class="btn btn-sm btn-outline-primary rounded-pill" href="/cms?collection=<?= urlencode($collection) ?>&id=<?= urlencode($doc['_id']) ?>">Edit</a><form class="d-inline" method="post" action="cms-delete.php" onsubmit="return confirm('Delete this content?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><input type="hidden" name="collection" value="<?= htmlspecialchars($collection) ?>"><input type="hidden" name="id" value="<?= htmlspecialchars($doc['_id']) ?>"><button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button></form></td></tr>
<?php endforeach; ?>
<?php if (!$documents): ?><tr><td colspan="4" class="text-center py-5 text-muted">No content yet. Create the first item.</td></tr><?php endif; ?>
</tbody></table></div></div></div>
<?php if (isset($_GET['new']) || $editing): ?>
<div class="card cms-card"><div class="card-body p-4">
<form method="post" action="cms-save.php">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
<input type="hidden" name="collection" value="<?= htmlspecialchars($collection) ?>">
<input type="hidden" name="id" value="<?= htmlspecialchars($editing['_id'] ?? '') ?>">
<div class="row g-3">
<?php foreach ($fields as $key => $value): ?>
<div class="col-12 <?= is_array($value) || is_object($value) || in_array($key,['Content','Description','Notes','Options'],true) ? '' : 'col-lg-6' ?>">
<label class="form-label fw-semibold"><?= htmlspecialchars($key) ?></label>
<?php if (is_array($value) || is_object($value) || in_array($key,['Content','Description','Notes','Options'],true)): ?><textarea class="form-control json-field" name="fields[<?= htmlspecialchars($key) ?>]"><?= htmlspecialchars(cms_value($value)) ?></textarea><?php else: ?><input class="form-control" name="fields[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars(cms_value($value)) ?>"><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><a href="/cms?collection=<?= urlencode($collection) ?>" class="btn btn-light rounded-pill">Cancel</a><button class="btn btn-primary rounded-pill px-4">Save content</button></div>
</form>
</div></div>
<?php endif; ?>
</main>
</div>
</body>
</html>
