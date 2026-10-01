<?php
require_once __DIR__ . '/config.php';

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => app_base_path() . '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cms-init.php';

$admin_password_hash = admin_password_hash(); 

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header('Location: ' . app_path('/admin'));
    exit;
}

$maxAttempts = 5;
$lockoutSeconds = 60;
if (!isset($_SESSION['login_attempts'])) $_SESSION['login_attempts'] = 0;
if (!isset($_SESSION['login_locked_until'])) $_SESSION['login_locked_until'] = 0;

if (isset($_POST['login'])) {
    if (!verify_csrf_token($_POST['csrf_token'] ?? null)) {
        $login_error = "Your session expired. Please refresh and try again.";
    } elseif ($admin_password_hash === null) {
        $login_error = "Admin authentication is not configured.";
    } elseif (time() < $_SESSION['login_locked_until']) {
        $login_error = "Too many attempts. Please try again in a minute.";
    } elseif (password_verify($_POST['password'] ?? '', $admin_password_hash)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['login_attempts'] = 0;
    } else {
        $_SESSION['login_attempts']++;
        if ($_SESSION['login_attempts'] >= $maxAttempts) {
            $_SESSION['login_locked_until'] = time() + $lockoutSeconds;
            $_SESSION['login_attempts'] = 0;
        }
        $login_error = "Invalid Password";
    }
}

$bookings = [];
$paidCount = 0;
$pendingCount = 0;
$totalRevenue = 0.0;
if (isset($_SESSION['admin_logged_in'])) {
    try {
        $db = new PDO("sqlite:" . __DIR__ . "/database/bookings.db");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        $stmt = $db->query("SELECT * FROM bookings ORDER BY created_at DESC");
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        foreach ($bookings as $booking) {
            if ((int) ($booking['paid'] ?? 0) === 1) {
                $paidCount++;
                $totalRevenue += (float) ($booking['price'] ?? 0);
            } else {
                $pendingCount++;
            }
        }
    } catch (Exception $e) {
        error_log("admin.php database error: " . $e->getMessage());
        $db_error = "The dashboard could not load its data right now.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>SmartStudyPro Administration</title>
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
<link href="assets/css/main.css" rel="stylesheet">
<link href="assets/css/responsive.css" rel="stylesheet">
<link rel="shortcut icon" href="Smart_Study_Logo_Fin-removebg-preview.png" type="image/x-icon">
<style>
:root{--ssp-navy:#0c086b;--ssp-orange:#e66a00;--ssp-blue:#2447d8}body{background:linear-gradient(135deg,#f8faff 0%,#eef3ff 55%,#fff8f1 100%)}.admin-shell{min-height:100vh}.admin-side{width:270px;background:linear-gradient(180deg,#0c086b 0%,#17118f 55%,#0c086b 100%);color:#fff;flex:0 0 270px;box-shadow:10px 0 35px rgba(12,8,107,.14)}.admin-side a{color:rgba(255,255,255,.82);text-decoration:none;border-radius:14px;padding:11px 13px;display:flex;align-items:center;gap:10px}.admin-side a:hover,.admin-side a.active{background:rgba(255,255,255,.13);color:#fff}.admin-main{min-width:0}.admin-card{border:0;border-radius:22px;box-shadow:0 12px 40px rgba(12,8,107,.08)}.admin-action{transition:transform .18s ease,box-shadow .18s ease}.admin-action:hover{transform:translateY(-2px);box-shadow:0 14px 35px rgba(12,8,107,.12)}.admin-icon{width:46px;height:46px;border-radius:14px;display:grid;place-items:center;background:#eef1ff;color:#0c086b;font-size:1.2rem}@media(max-width:800px){.admin-side{width:76px;flex-basis:76px}.admin-side .brand-text,.admin-side .nav-text{display:none}.admin-side a{justify-content:center}.admin-side .nav-section{display:none}}
</style>
</head>
<body>
<div class="admin-shell d-flex">
<aside class="admin-side p-3">
<div class="mb-4"><img src="<?= htmlspecialchars(app_path("/Smart_Study_Logo_Fin-removebg-preview.png"), ENT_QUOTES, "UTF-8") ?>" alt="SmartStudyPro" style="width:150px;max-width:100%;height:auto;display:block"><div class="small text-white-50 mt-2 brand-text">Administration</div></div>
<div class="small text-white-50 mb-2 nav-section">ADMINISTRATION</div>
<nav class="d-grid gap-1">
<a class="active" href="<?= htmlspecialchars(app_path('/admin'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-speedometer2"></i><span class="nav-text">Dashboard</span></a>
<a href="<?= htmlspecialchars(app_path('/cms'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-grid-1x2"></i><span class="nav-text">Content CMS</span></a>
<a href="<?= htmlspecialchars(app_path('/cms?collection=Courses'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-mortarboard"></i><span class="nav-text">Courses</span></a>
<a href="<?= htmlspecialchars(app_path('/cms?collection=Lessons'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-play-circle"></i><span class="nav-text">Lessons</span></a>
<a href="<?= htmlspecialchars(app_path('/cms?collection=Quizzes'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-patch-question"></i><span class="nav-text">Quizzes</span></a>
<a href="<?= htmlspecialchars(app_path('/cms?collection=Products'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-bag"></i><span class="nav-text">Products</span></a>
<a href="<?= htmlspecialchars(app_path('/cms/media'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-images"></i><span class="nav-text">Media Library</span></a>
</nav>
<div class="small text-white-50 mt-4 mb-2 nav-section">SYSTEM</div>
<nav class="d-grid gap-1">
<a href="<?= htmlspecialchars(app_path('/docs/INSTALL.md'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-book"></i><span class="nav-text">Documentation</span></a>
<a href="<?= htmlspecialchars(app_path('/error.php?code=404'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-exclamation-triangle"></i><span class="nav-text">Error Pages</span></a>
<a href="<?= htmlspecialchars(app_path('/'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-house"></i><span class="nav-text">View Website</span></a>
<a href="<?= htmlspecialchars(app_path('/logout'), ENT_QUOTES, 'UTF-8') ?>"><i class="bi bi-box-arrow-right"></i><span class="nav-text">Logout</span></a>
</nav>
</aside>
<main class="admin-main flex-grow-1 p-3 p-lg-5">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
<div><div class="small text-muted">SmartStudyPro administration</div><h1 class="h2 fw-bold mb-1">Welcome to your control center</h1><div class="text-muted">Manage the website, learning content, media and orders from one place.</div></div>
<a href="<?= htmlspecialchars(app_path('/'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-outline-dark rounded-pill px-4"><i class="bi bi-box-arrow-up-right me-1"></i> View website</a>
</div>

<div class="card admin-card mb-4">
<div class="card-body p-4 p-lg-5">
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
<div><div class="badge text-bg-primary rounded-pill mb-2">Native SmartStudyPro CMS</div><h2 class="h4 fw-bold">Your content is ready to manage</h2><p class="text-muted mb-0">Edit the content migrated from Cockpit without opening the legacy CMS.</p></div>
<a href="<?= htmlspecialchars(app_path('/cms'), ENT_QUOTES, 'UTF-8') ?>" class="btn btn-primary rounded-pill px-4"><i class="bi bi-grid-1x2 me-1"></i> Open CMS</a>
</div>
</div>
</div>

<div class="row g-3 mb-4">
<div class="col-12 col-sm-6 col-xl-3"><a class="text-decoration-none text-dark" href="<?= htmlspecialchars(app_path('/cms?collection=HomePage'), ENT_QUOTES, 'UTF-8') ?>"><div class="card admin-card admin-action h-100"><div class="card-body p-4"><div class="admin-icon mb-3"><i class="bi bi-house"></i></div><h3 class="h6 fw-bold">Home Page</h3><p class="small text-muted mb-0">Edit homepage content and SEO fields.</p></div></div></a></div>
<div class="col-12 col-sm-6 col-xl-3"><a class="text-decoration-none text-dark" href="<?= htmlspecialchars(app_path('/cms?collection=Courses'), ENT_QUOTES, 'UTF-8') ?>"><div class="card admin-card admin-action h-100"><div class="card-body p-4"><div class="admin-icon mb-3"><i class="bi bi-mortarboard"></i></div><h3 class="h6 fw-bold">Courses</h3><p class="small text-muted mb-0">Manage courses, prices, descriptions and images.</p></div></div></a></div>
<div class="col-12 col-sm-6 col-xl-3"><a class="text-decoration-none text-dark" href="<?= htmlspecialchars(app_path('/cms?collection=Lessons'), ENT_QUOTES, 'UTF-8') ?>"><div class="card admin-card admin-action h-100"><div class="card-body p-4"><div class="admin-icon mb-3"><i class="bi bi-play-circle"></i></div><h3 class="h6 fw-bold">Lessons</h3><p class="small text-muted mb-0">Manage lesson content and learning media.</p></div></div></a></div>
<div class="col-12 col-sm-6 col-xl-3"><a class="text-decoration-none text-dark" href="<?= htmlspecialchars(app_path('/cms/media'), ENT_QUOTES, 'UTF-8') ?>"><div class="card admin-card admin-action h-100"><div class="card-body p-4"><div class="admin-icon mb-3"><i class="bi bi-images"></i></div><h3 class="h6 fw-bold">Media Library</h3><p class="small text-muted mb-0">Upload and manage images, video and documents.</p></div></div></a></div>
</div>

<div class="card admin-card mb-4">
<div class="card-body p-4">
<div class="d-flex justify-content-between align-items-center mb-3"><div><h2 class="h5 fw-bold mb-1">Orders & bookings</h2><div class="small text-muted">Financial activity from the existing SmartStudyPro booking system.</div></div><span class="badge bg-primary-subtle text-primary rounded-pill"><?= count($bookings) ?> entries</span></div>
<div class="row g-3 mb-4">
<div class="col-md-4"><div class="p-3 rounded-4 bg-light"><div class="small text-muted">Paid orders</div><div class="fs-3 fw-bold text-success"><?= $paidCount ?></div></div></div>
<div class="col-md-4"><div class="p-3 rounded-4 bg-light"><div class="small text-muted">Pending orders</div><div class="fs-3 fw-bold text-warning"><?= $pendingCount ?></div></div></div>
<div class="col-md-4"><div class="p-3 rounded-4 bg-light"><div class="small text-muted">Paid revenue</div><div class="fs-3 fw-bold">UGX <?= number_format($totalRevenue) ?></div></div></div>
</div>
<div class="input-group mb-3"><span class="input-group-text bg-white"><i class="bi bi-search"></i></span><input id="bookingSearch" type="search" class="form-control" placeholder="Search bookings"></div>
<div class="table-responsive"><table id="bookingsTable" class="table table-hover align-middle mb-0"><thead><tr><th>Date</th><th>Customer</th><th>Service</th><th>Contact</th><th>Amount</th><th>Status</th></tr></thead><tbody>
<?php foreach ($bookings as $row): ?>
<tr><td class="small text-muted"><?= date('M d, Y', strtotime($row['created_at'])) ?></td><td><div class="fw-bold"><?= htmlspecialchars($row['name']) ?></div><div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div></td><td><?= htmlspecialchars($row['service']) ?></td><td><?= htmlspecialchars($row['phone']) ?></td><td class="fw-bold">UGX <?= number_format((float)$row['price']) ?></td><td><span class="badge rounded-pill <?= $row['paid'] ? 'bg-success' : 'bg-warning text-dark' ?>"><?= $row['paid'] ? 'Paid' : 'Pending' ?></span></td></tr>
<?php endforeach; ?>
<?php if (!$bookings): ?><tr><td colspan="6" class="text-center py-5 text-muted">No bookings found.</td></tr><?php endif; ?>
</tbody></table></div>
</div></div>

<div class="row g-3">
<div class="col-lg-6"><div class="card admin-card h-100"><div class="card-body p-4"><h2 class="h5 fw-bold"><i class="bi bi-book me-2"></i>Documentation</h2><p class="text-muted">Installation, configuration, CMS migration, media, payments, security and troubleshooting instructions.</p><a class="btn btn-outline-primary rounded-pill" href="docs/INSTALL.md">Open setup documentation</a></div></div></div>
<div class="col-lg-6"><div class="card admin-card h-100"><div class="card-body p-4"><h2 class="h5 fw-bold"><i class="bi bi-shield-exclamation me-2"></i>Error pages</h2><p class="text-muted">Preview the branded SmartStudyPro error experience.</p><div class="d-flex flex-wrap gap-2"><a class="btn btn-outline-danger rounded-pill" href="<?= htmlspecialchars(app_path('/error.php?code=404'), ENT_QUOTES, 'UTF-8') ?>">404 Preview</a><a class="btn btn-outline-danger rounded-pill" href="<?= htmlspecialchars(app_path('/error.php?code=500'), ENT_QUOTES, 'UTF-8') ?>">500 Preview</a></div></div></div></div>
</div>
</main>
</div>
<script>
document.getElementById('bookingSearch')?.addEventListener('input',function(){const q=this.value.trim().toLowerCase();document.querySelectorAll('#bookingsTable tbody tr').forEach(r=>r.style.display=!q||r.textContent.toLowerCase().includes(q)?'':'none')});
</script>
</body>
</html>