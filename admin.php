<?php
session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'httponly' => true,
    'samesite' => 'Lax',
    'secure'   => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
]);
session_start();


require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/cms-init.php';

$admin_password_hash = admin_password_hash(); 

if (isset($_GET['logout'])) {
    $_SESSION = [];
    session_destroy();
    header("Location: admin.php");
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
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Admin Dashboard - SmartStudyPro</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
<link href="assets/css/responsive.css" rel="stylesheet">
  <link rel="shortcut icon" href="Smart_Study_Logo_Fin-removebg-preview.png" type="image/x-icon">
</head>
<body class="bg-light">

  <?php if (!isset($_SESSION['admin_logged_in'])): ?>
    <div class="container d-flex justify-content-center align-items-center" style="min-height: 100vh;">
      <div class="card shadow-sm p-4" style="width: 100%; max-width: 400px; border-radius: 15px;">
        <div class="text-center mb-4">
            <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="Logo" style="height: 60px;">
            <h4 class="fw-bold mt-3">Admin Login</h4>
        </div>
        <form method="POST">
          <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
          <div class="mb-3">
            <label class="form-label">Password</label>
            <input type="password" name="password" class="form-control rounded-pill" required>
          </div>
          <?php if(isset($login_error)) echo "<p class='text-danger small'>$login_error</p>"; ?>
          <button type="submit" name="login" class="btn btn-success w-100 rounded-pill" style="background:#5fcf80; border:none;">Access Dashboard</button>
        </form>
      </div>
    </div>
  <?php else: ?>

    <nav class="navbar navbar-dark bg-dark shadow-sm">
      <div class="container">
        <a class="navbar-brand fw-bold" href="#">SmartStudyPro Panel</a>
        <a href="?logout=1" class="btn btn-outline-light btn-sm rounded-pill">Logout</a>
      </div>
    </nav>

    <main class="container py-5">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold">Course Bookings & Orders</h2>
        <span class="badge bg-success rounded-pill"><?= count($bookings) ?> Total Entries</span>
      </div>

      <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4"><div class="small text-muted mb-1">Paid Orders</div><div class="fs-2 fw-bold text-success"><?= $paidCount ?></div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4"><div class="small text-muted mb-1">Pending Orders</div><div class="fs-2 fw-bold text-warning"><?= $pendingCount ?></div></div></div></div>
        <div class="col-md-4"><div class="card border-0 shadow-sm rounded-4 h-100"><div class="card-body p-4"><div class="small text-muted mb-1">Paid Revenue</div><div class="fs-2 fw-bold">UGX <?= number_format($totalRevenue) ?></div></div></div></div>
      </div>
      <?php if (isset($cockpit_message)): ?>
        <div class="alert alert-info border-0 rounded-4"><?= htmlspecialchars($cockpit_message) ?></div>
      <?php endif; ?>

      <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
          <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
              <div class="fw-bold" style="color:#0C086B;">SmartStudyPro CMS</div>
              <div class="small text-muted">Manage courses, lessons, quizzes, products, pages, media-ready content and site settings from one focused administration area.</div>
            </div>
            <div><a href="cms.php" class="btn btn-primary rounded-pill px-3"><i class="bi bi-grid-1x2 me-1"></i> Open SmartStudyPro CMS</a></div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm rounded-4 mb-4"><div class="card-body p-3"><div class="input-group"><span class="input-group-text bg-white border-0"><i class="bi bi-search"></i></span><input id="bookingSearch" type="search" class="form-control border-0 shadow-none" placeholder="Search customers, email, products, phone, or status"></div></div></div>

      <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
          <table id="bookingsTable" class="table table-hover align-middle mb-0">
            <thead class="bg-light">
              <tr>
                <th class="px-4">Date</th>
                <th>Student/Customer</th>
                <th>Item/Service</th>
                <th>Contact</th>
                <th>Amount</th>
                <th class="text-center">Status</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($bookings as $row): ?>
              <tr>
                <td class="px-4 small text-muted"><?= date('M d, Y', strtotime($row['created_at'])) ?></td>
                <td>
                  <div class="fw-bold"><?= htmlspecialchars($row['name']) ?></div>
                  <div class="small text-muted"><?= htmlspecialchars($row['email']) ?></div>
                </td>
                <td><?= htmlspecialchars($row['service']) ?></td>
                <td><?= htmlspecialchars($row['phone']) ?></td>
                <td class="fw-bold text-success">UGX <?= number_format((float)$row['price']) ?></td>
                <td class="text-center">
                  <span class="badge rounded-pill <?= $row['paid'] ? 'bg-success' : 'bg-warning text-dark' ?>">
                    <?= $row['paid'] ? 'Paid' : 'Pending' ?>
                  </span>
                </td>
              </tr>
              <?php endforeach; ?>
              <?php if(empty($bookings)): ?>
                <tr><td colspan="6" class="text-center py-5 text-muted">No bookings found in the database.</td></tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </main>
  <?php endif; ?>

  <script>
    document.getElementById('bookingSearch')?.addEventListener('input', function () {
      const query = this.value.trim().toLowerCase();
      document.querySelectorAll('#bookingsTable tbody tr').forEach(function (row) {
        row.style.display = !query || row.textContent.toLowerCase().includes(query) ? '' : 'none';
      });
    });
  </script>
</body>
</html>