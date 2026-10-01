<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/app/Services/SmartStudyProCms.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: ' . app_path('/admin'));
    exit;
}

$collections = SmartStudyProCms::collections();
$known = ['HomePage','AboutPage','ContactDetails','Products','Courses','Chapters','Lessons','Quizzes','Pages','Navigation','SiteSettings'];
$collections = array_values(array_unique(array_merge($known, $collections)));
$collection = trim((string) ($_GET['collection'] ?? 'HomePage'));
if (!in_array($collection, $collections, true)) {
    $collection = 'HomePage';
}

$search = trim((string) ($_GET['q'] ?? ''));
$sort = trim((string) ($_GET['sort'] ?? 'updated'));
$direction = strtolower(trim((string) ($_GET['dir'] ?? 'desc'))) === 'asc' ? 'asc' : 'desc';
$documents = $sort === 'updated' ? SmartStudyProCms::items($collection, ['sort' => ['updated_at' => $direction === 'asc' ? 1 : -1]]) : SmartStudyProCms::items($collection);
if ($sort === 'title') {
    usort($documents, static function (array $a, array $b) use ($direction): int {
        $cmp = strnatcasecmp((string) ($a['Title'] ?? $a['title'] ?? $a['Name'] ?? $a['name'] ?? $a['_id']), (string) ($b['Title'] ?? $b['title'] ?? $b['Name'] ?? $b['name'] ?? $b['_id']));
        return $direction === 'asc' ? $cmp : -$cmp;
    });
}
if ($search !== '') {
    $needle = mb_strtolower($search);
    $documents = array_values(array_filter($documents, static function (array $doc) use ($needle): bool {
        return str_contains(mb_strtolower(json_encode($doc, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)), $needle);
    }));
}
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
body{background:#f5f7fb}.cms-shell{min-height:100vh}.cms-sidebar{width:260px;background:#0c086b;color:#fff}.cms-brand{font-weight:800}.cms-nav a{color:rgba(255,255,255,.78);text-decoration:none;border-radius:12px;padding:10px 12px;display:block}.cms-nav a:hover,.cms-nav a.active{background:rgba(255,255,255,.12);color:#fff}.cms-main{min-width:0}.cms-card{border:0;border-radius:20px;box-shadow:0 12px 40px rgba(12,8,107,.08)}.cms-table td,.cms-table th{vertical-align:middle}.field-card{border:1px solid #e8ebf2;border-radius:16px}.json-field{min-height:180px;font-family:ui-monospace,monospace}.rich-editor{min-height:220px;overflow:auto}.rich-editor:focus{box-shadow:0 0 0 .2rem rgba(13,110,253,.15)}.cms-sidebar{flex-shrink:0}@media(max-width:900px){.cms-sidebar{width:82px}.cms-sidebar .label,.cms-sidebar .cms-brand span{display:none}.cms-sidebar .cms-nav a{text-align:center}.cms-sidebar .cms-nav i{margin:0!important}}
</style>
</head>
<body>
<div class="cms-shell d-flex">
<aside class="cms-sidebar p-3">
<div class="cms-brand fs-5 mb-4"><i class="bi bi-mortarboard-fill me-2"></i><span>SmartStudyPro CMS</span></div>
<div class="small text-white-50 mb-2 label">CONTENT</div>
<nav class="cms-nav d-grid gap-1">
<?php foreach ($known as $name): ?>
<a class="<?= $collection === $name ? 'active' : '' ?>" href="<?= htmlspecialchars(app_path('/cms?collection=<?= urlencode($name) ?>"><i class="bi bi-grid me-2"></i><span class="label"><?= htmlspecialchars($name) ?></span></a>
<?php endforeach; ?>
</nav>
<div class="small text-white-50 mt-4 mb-2 label">SYSTEM</div>
<a class="cms-nav text-white text-decoration-none" href="/cms/media"><i class="bi bi-images me-2"></i><span class="label">Media Library</span></a>
<a class="cms-nav text-white text-decoration-none" href="admin.php"><i class="bi bi-arrow-left me-2"></i><span class="label">Admin Dashboard</span></a>
</aside>
<main class="cms-main flex-grow-1 p-3 p-lg-5">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
<div><div class="text-muted small">SmartStudyPro content management</div><h1 class="h3 fw-bold mb-0"><?= htmlspecialchars($collection) ?></h1></div>
<div class="d-flex flex-wrap gap-2"><a href="<?= htmlspecialchars(app_path('/cms?collection=<?= urlencode($collection) ?>&new=1" class="btn btn-primary rounded-pill px-4"><i class="bi bi-plus-lg me-1"></i>New content</a><a href="/cms/media" class="btn btn-outline-primary rounded-pill px-4"><i class="bi bi-images me-1"></i>Media</a></div>
</div>
<?php if (isset($_GET['saved'])): ?><div class="alert alert-success rounded-4 border-0">Content saved successfully.</div><?php endif; ?>
<?php if (isset($_GET['deleted'])): ?><div class="alert alert-success rounded-4 border-0"><?= (int) $_GET['deleted'] ?> content item(s) deleted successfully.</div><?php endif; ?>
<div class="card cms-card mb-4"><div class="card-body p-3 p-lg-4"><form method="get" class="row g-2 align-items-end mb-3"><input type="hidden" name="collection" value="<?= htmlspecialchars($collection) ?>"><div class="col-12 col-lg-7"><label class="form-label small fw-semibold">Search content</label><input class="form-control" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search title, description, ID or any field"></div><div class="col-6 col-lg-2"><label class="form-label small fw-semibold">Sort</label><select class="form-select" name="sort"><option value="title" <?= $sort === 'title' ? 'selected' : '' ?>>Title</option><option value="updated" <?= $sort === 'updated' ? 'selected' : '' ?>>Recently updated</option></select></div><div class="col-6 col-lg-2"><label class="form-label small fw-semibold">Order</label><select class="form-select" name="dir"><option value="desc" <?= $direction === 'desc' ? 'selected' : '' ?>>Descending</option><option value="asc" <?= $direction === 'asc' ? 'selected' : '' ?>>Ascending</option></select></div><div class="col-12 col-lg-1"><button class="btn btn-primary w-100">Filter</button></div></form><form method="post" action="cms-bulk.php" id="bulk-form"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><input type="hidden" name="collection" value="<?= htmlspecialchars($collection) ?>"><div class="d-flex flex-wrap align-items-center gap-2 mb-3"><label class="d-flex align-items-center gap-2 small fw-semibold"><input type="checkbox" id="select-all" class="form-check-input mt-0"> Select all</label><select name="action" id="bulk-action" class="form-select form-select-sm w-auto"><option value="delete">Delete selected</option></select><button class="btn btn-sm btn-outline-danger rounded-pill" id="bulk-apply" type="submit">Apply</button><span class="small text-muted"><?= count($documents) ?> item<?= count($documents) === 1 ? '' : 's' ?></span></div></form><div class="table-responsive"><table class="table cms-table mb-0"><thead><tr><th class="px-4"><span class="visually-hidden">Select</span></th><th>ID</th><th>Title / Name</th><th>Preview</th><th class="text-end px-4">Actions</th></tr></thead><tbody>
<?php foreach ($documents as $doc): $title = $doc['Title'] ?? $doc['title'] ?? $doc['Name'] ?? $doc['name'] ?? $doc['_id']; $preview = $doc['Content'] ?? $doc['Description'] ?? $doc['content'] ?? ''; ?>
<tr><td class="px-4"><input form="bulk-form" type="checkbox" class="form-check-input bulk-item" name="ids[]" value="<?= htmlspecialchars($doc['_id']) ?>"></td><td class="small text-muted"><?= htmlspecialchars((string)$doc['_id']) ?></td><td class="fw-semibold"><?= htmlspecialchars((string)$title) ?></td><td class="text-muted"><?= htmlspecialchars(mb_strimwidth(strip_tags(cms_value($preview)),0,100,'…')) ?></td><td class="text-end px-4"><a class="btn btn-sm btn-outline-primary rounded-pill" href="<?= htmlspecialchars(app_path('/cms?collection=<?= urlencode($collection) ?>&id=<?= urlencode($doc['_id']) ?>">Edit</a><form class="d-inline" method="post" action="cms-delete.php" onsubmit="return confirm('Delete this content?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><input type="hidden" name="collection" value="<?= htmlspecialchars($collection) ?>"><input type="hidden" name="id" value="<?= htmlspecialchars($doc['_id']) ?>"><button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button></form></td></tr>
<?php endforeach; ?>
<?php if (!$documents): ?><tr><td colspan="4" class="text-center py-5 text-muted">No content yet. Create the first item.</td></tr><?php endif; ?>
</tbody></table></div></div></div><script>document.addEventListener('DOMContentLoaded',()=>{const all=document.getElementById('select-all'),items=[...document.querySelectorAll('.bulk-item')],form=document.getElementById('bulk-form');all?.addEventListener('change',()=>items.forEach(i=>i.checked=all.checked));form?.addEventListener('submit',e=>{if(!items.some(i=>i.checked)){e.preventDefault();return}if(!confirm('Apply this action to the selected content?'))e.preventDefault()});});</script>
<?php if ($search !== ''): ?><div class="small text-muted mb-3">Showing results for <strong><?= htmlspecialchars($search) ?></strong>.</div><?php endif; ?>
<?php if (isset($_GET['new']) || $editing): ?>
<div class="card cms-card"><div class="card-body p-4">
<form method="post" action="cms-save.php">
<input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>">
<input type="hidden" name="collection" value="<?= htmlspecialchars($collection) ?>">
<input type="hidden" name="id" value="<?= htmlspecialchars($editing['_id'] ?? '') ?>">
<div class="row g-3">
<?php foreach ($fields as $key => $value): ?>
<?php $lowerKey = strtolower($key); $isRich = in_array($key, ['Content','Description'], true) && !is_array($value) && !is_object($value); $isStructured = is_array($value) || is_object($value) || in_array($key, ['Notes','Options','Items'], true); $fieldType = $isStructured || $isRich ? 'textarea' : (str_contains($lowerKey,'email') ? 'email' : (str_contains($lowerKey,'price') || $lowerKey === 'order' ? 'number' : (str_contains($lowerKey,'image') || str_contains($lowerKey,'file') || str_contains($lowerKey,'video') ? 'url' : 'text'))); ?>
<div class="col-12 <?= $isStructured || $isRich ? '' : 'col-lg-6' ?>">
<label class="form-label fw-semibold" for="field-<?= htmlspecialchars($key) ?>"><?= htmlspecialchars($key) ?></label>
<?php if ($isRich): ?>
<div class="btn-toolbar mb-2 gap-1" data-editor-toolbar="field-<?= htmlspecialchars($key) ?>"><button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="bold"><i class="bi bi-type-bold"></i></button><button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="italic"><i class="bi bi-type-italic"></i></button><button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="insertUnorderedList"><i class="bi bi-list-ul"></i></button><button type="button" class="btn btn-sm btn-outline-secondary" data-cmd="createLink"><i class="bi bi-link-45deg"></i></button></div><div id="field-<?= htmlspecialchars($key) ?>" class="form-control rich-editor" contenteditable="true" data-target="hidden-<?= htmlspecialchars($key) ?>" data-value="<?= htmlspecialchars(cms_value($value), ENT_QUOTES) ?>"></div><input type="hidden" id="hidden-<?= htmlspecialchars($key) ?>" name="fields[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars(cms_value($value)) ?>">
<?php elseif ($fieldType === 'textarea'): ?><textarea id="field-<?= htmlspecialchars($key) ?>" class="form-control json-field" name="fields[<?= htmlspecialchars($key) ?>]"><?= htmlspecialchars(cms_value($value)) ?></textarea>
<?php else: ?><div class="input-group"><input id="field-<?= htmlspecialchars($key) ?>" class="form-control" type="<?= $fieldType ?>" name="fields[<?= htmlspecialchars($key) ?>]" value="<?= htmlspecialchars(cms_value($value)) ?>"><?php if ($fieldType === 'url' && (str_contains($lowerKey,'image') || str_contains($lowerKey,'file') || str_contains($lowerKey,'video'))): ?><a class="btn btn-outline-primary" href="/cms/media" target="_blank" rel="noopener">Media Library</a><?php endif; ?></div><?php endif; ?>
<?php if ($isStructured): ?><div class="form-text">Structured data is stored as JSON. Use valid JSON when entering arrays or objects.</div><?php elseif ($isRich): ?><div class="form-text">Use the editor for formatted content. Links and lists are supported.</div><?php endif; ?>
</div>
<?php endforeach; ?>
</div>
<div class="d-flex justify-content-end gap-2 mt-4"><a href="<?= htmlspecialchars(app_path('/cms?collection=<?= urlencode($collection) ?>" class="btn btn-light rounded-pill">Cancel</a><button class="btn btn-primary rounded-pill px-4">Save content</button></div>
</form>
</div></div>
<script>
document.addEventListener('DOMContentLoaded',()=>{
document.querySelectorAll('.rich-editor').forEach(editor=>{editor.innerHTML=editor.dataset.value||''});
document.querySelectorAll('[data-editor-toolbar]').forEach(toolbar=>toolbar.querySelectorAll('[data-cmd]').forEach(button=>button.addEventListener('click',()=>{
const editor=document.getElementById(toolbar.dataset.editorToolbar);editor.focus();
if(button.dataset.cmd==='createLink'){const url=prompt('Enter URL');if(url)document.execCommand('createLink',false,url)}else document.execCommand(button.dataset.cmd,false,null);
const target=document.getElementById(editor.dataset.target);target.value=editor.innerHTML;
})));
document.querySelectorAll('.rich-editor').forEach(editor=>editor.addEventListener('input',()=>{document.getElementById(editor.dataset.target).value=editor.innerHTML}));

});
</script>
<?php endif; ?>
</main>
</div>
</body>
</html>
