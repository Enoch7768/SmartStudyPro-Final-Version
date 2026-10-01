<?php

require_once __DIR__ . '/auth.php';

if (empty($_SESSION['admin_logged_in'])) {
    header('Location: admin.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        http_response_code(403);
        exit('Invalid request.');
    }

    $file = $_FILES['file'] ?? null;
    if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
        $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
        $reason = match ($error) {
            UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'size',
            UPLOAD_ERR_PARTIAL => 'partial',
            UPLOAD_ERR_NO_TMP_DIR, UPLOAD_ERR_CANT_WRITE => 'server',
            default => 'upload',
        };
        header('Location: ' . app_path('/cms/media') . '?error=' . $reason);
        exit;
    }
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mime = $finfo->file($file['tmp_name']);
    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        'video/mp4' => 'mp4',
        'application/pdf' => 'pdf',
        'application/zip' => 'zip',
        'application/x-zip-compressed' => 'zip',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document' => 'docx',
        'application/msword' => 'doc',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' => 'xlsx',
        'application/vnd.ms-excel' => 'xls',
        'text/plain' => 'txt',
    ];

    if (!isset($allowed[$mime])) {
        header('Location: ' . app_path('/cms/media') . '?error=type');
        exit;
    }

    $dir = __DIR__ . '/uploads/cms';
    if (!is_dir($dir)) {
        mkdir($dir, 0750, true);
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $allowed[$mime];
    if (!move_uploaded_file($file['tmp_name'], $dir . '/' . $filename)) {
        header('Location: ' . app_path('/cms/media') . '?error=save');
        exit;
    }

    header('Location: ' . app_path('/cms/media') . '?saved=1');
    exit;
}

$search = trim((string) ($_GET['q'] ?? ''));
$files = [];
$dir = __DIR__ . '/uploads/cms';
if (is_dir($dir)) {
    foreach (scandir($dir) as $name) {
        if ($name === '.' || $name === '..') {
            continue;
        }
        $path = $dir . '/' . $name;
        if (is_file($path)) {
            if ($search === '' || str_contains(mb_strtolower($name), mb_strtolower($search))) {
                $mimeType = (new finfo(FILEINFO_MIME_TYPE))->file($path);
                $files[] = ['name' => $name, 'size' => filesize($path), 'mime' => $mimeType, 'url' => 'uploads/cms/' . rawurlencode($name)];
            }
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SmartStudyPro CMS Media</title>
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/main.css" rel="stylesheet">
<link href="assets/css/responsive.css" rel="stylesheet">
<style>body{background:#f5f7fb}.shell{min-height:100vh}.side{width:260px;background:#0c086b;color:#fff}.side a{display:block;color:#fff;text-decoration:none;padding:10px 12px;border-radius:12px}.side a:hover{background:rgba(255,255,255,.12)}.cardx{border:0;border-radius:20px;box-shadow:0 12px 40px rgba(12,8,107,.08)}.thumb{height:150px;object-fit:cover;border-radius:14px}</style>
</head>
<body>
<div class="shell d-flex">
<aside class="side p-3">
<div class="fw-bold fs-5 mb-4"><i class="bi bi-mortarboard-fill me-2"></i>SmartStudyPro CMS</div>
<a href="<?= htmlspecialchars(app_path('/cms'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-grid me-2"></i>Content</a>
<a href="<?= htmlspecialchars(app_path('/cms/media'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-images me-2"></i>Media</a>
<a href="admin.php"><i class="bi bi-arrow-left me-2"></i>Admin Dashboard</a>
</aside>
<main class="flex-grow-1 p-4 p-lg-5">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4"><div><div class="small text-muted">SmartStudyPro CMS</div><h1 class="h3 fw-bold mb-0">Media Library</h1></div><form method="get" class="d-flex gap-2"><input class="form-control" name="q" value="<?= htmlspecialchars($search) ?>" placeholder="Search media"><button class="btn btn-outline-primary rounded-pill">Search</button></form></div>
<?php if(isset($_GET['saved'])): ?><div class="alert alert-success rounded-4 border-0">Media uploaded successfully.</div><?php endif; ?><?php if(isset($_GET['deleted'])): ?><div class="alert alert-success rounded-4 border-0">Media deleted successfully.</div><?php endif; ?>
<?php if(isset($_GET['error'])): ?><div class="alert alert-danger rounded-4 border-0"><?= match ($_GET['error'] ?? '') { 'size' => 'The server rejected the upload because its PHP upload configuration is limiting the request. SmartStudyPro itself has no media-size cap.', 'partial' => 'The upload was interrupted before it completed. Please try again.', 'server' => 'The server could not write the uploaded file. Check temporary storage and permissions.', 'type' => 'This file type is not supported by the CMS media library.', default => 'The upload could not be completed. Check the file type, server configuration, or available storage.' } ?></div><?php endif; ?>
<div class="card cardx mb-4"><div class="card-body p-4"><form method="post" action="<?= htmlspecialchars(app_path('/cms/media'), ENT_QUOTES, 'UTF-8') ?>" enctype="multipart/form-data" class="d-flex flex-wrap gap-3 align-items-end"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><div><label class="form-label fw-semibold">Upload media</label><input class="form-control" type="file" name="file" accept="*/*" required></div><button class="btn btn-primary rounded-pill px-4">Upload</button></form></div></div>
<div class="row g-4">
<?php foreach($files as $file): ?>
<div class="col-sm-6 col-lg-4 col-xl-3"><div class="card cardx h-100"><div class="card-body"><?php if(str_starts_with($file['mime'],'image/')): ?><img class="w-100 thumb mb-3" src="<?= htmlspecialchars($file['url']) ?>" alt="<?= htmlspecialchars($file['name']) ?>"><?php elseif(str_starts_with($file['mime'],'video/')): ?><video class="w-100 thumb mb-3" controls preload="metadata"><source src="<?= htmlspecialchars($file['url']) ?>" type="<?= htmlspecialchars($file['mime']) ?>"></video><?php else: ?><div class="thumb mb-3 d-flex align-items-center justify-content-center bg-light"><i class="bi bi-file-earmark-text fs-1 text-primary"></i></div><?php endif; ?><div class="small fw-semibold text-break"><?= htmlspecialchars($file['name']) ?></div><div class="small text-muted"><?= htmlspecialchars($file['mime']) ?> · <?= number_format($file['size']/1024,1) ?> KB</div><div class="d-flex flex-wrap gap-2 mt-3"><a class="btn btn-sm btn-outline-primary rounded-pill" href="<?= htmlspecialchars($file['url']) ?>" target="_blank" rel="noopener">Open</a><button type="button" class="btn btn-sm btn-outline-secondary rounded-pill copy-url" data-url="<?= htmlspecialchars($file['url']) ?>">Copy URL</button><form method="post" action="cms-media-delete.php" class="d-inline" onsubmit="return confirm('Delete this media file?');"><input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token()) ?>"><input type="hidden" name="name" value="<?= htmlspecialchars($file['name']) ?>"><button class="btn btn-sm btn-outline-danger rounded-pill">Delete</button></form></div></div></div></div>
<?php endforeach; ?>
<?php if(!$files): ?><div class="col-12"><div class="card cardx"><div class="card-body py-5 text-center text-muted">No media uploaded yet.</div></div></div><?php endif; ?>
</div>
</main>
</div>
<script>document.addEventListener('click',async e=>{const b=e.target.closest('.copy-url');if(!b)return;try{await navigator.clipboard.writeText(new URL(b.dataset.url,location.href).href);const t=b.textContent;b.textContent='Copied';setTimeout(()=>b.textContent=t,1200)}catch(_){}});</script>
</body>
</html>
