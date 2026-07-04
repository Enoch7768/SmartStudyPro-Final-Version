<?php
require_once 'auth.php';
require_login();

$user = current_user();
$db = auth_db();
ensure_bookings_columns($db);

$stmt = $db->prepare("
    SELECT * FROM bookings
    WHERE paid = 1 AND (account_id = :account_id OR email = :email)
    ORDER BY created_at DESC
");
$stmt->execute([':account_id' => $user['id'], ':email' => $user['email']]);
$purchases = $stmt->fetchAll(PDO::FETCH_ASSOC);

$courses = [];
$otherItems = [];
foreach ($purchases as $item) {
    if (stripos($item['service'], 'Course') !== false) {
        $courses[] = $item;
    } else {
        $otherItems[] = $item;
    }
}

$initials = strtoupper(substr(trim($user['name']), 0, 1) ?: 'U');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>My Profile - SmartStudyPro</title>
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <style>
    :root { --study-green: #5fcf80; }
    .profile-hero {
      background: linear-gradient(135deg, var(--study-green), #3eb462);
      border-radius: 20px;
      color: #fff;
      padding: 40px;
    }
    .avatar-circle {
      width: 90px; height: 90px; border-radius: 50%;
      background: rgba(255,255,255,0.25);
      display: flex; align-items: center; justify-content: center;
      font-size: 2.2rem; font-weight: 700; color: #fff;
      border: 3px solid rgba(255,255,255,0.5);
      overflow: hidden;
    }
    .avatar-circle img { width: 100%; height: 100%; object-fit: cover; }
    .stat-card {
      background: #fff; border-radius: 15px; padding: 20px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05); text-align: center;
    }
    .course-card {
      background: #fff; border-radius: 15px; padding: 20px;
      box-shadow: 0 5px 20px rgba(0,0,0,0.05);
      display: flex; justify-content: space-between; align-items: center;
      margin-bottom: 15px;
    }
    .empty-state { text-align: center; padding: 60px 20px; color: #888; }
  </style>
</head>
<body class="bg-light">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="SmartStudyPro Logo">
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="courses.php">Courses</a></li>
          <li><a href="products.php">Products</a></li>
          <li><a href="cart.php"><i class="bi bi-bag"></i></a></li>
          <li><a href="profile.php" class="active"><i class="bi bi-person-circle"></i></a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
    </div>
  </header>

  <main class="container py-5">

    <div class="profile-hero d-flex align-items-center flex-wrap gap-4 mb-5">
      <div class="avatar-circle">
        <?php if (!empty($user['avatar'])): ?>
          <img src="<?= htmlspecialchars($user['avatar']) ?>" alt="Avatar">
        <?php else: ?>
          <?= htmlspecialchars($initials) ?>
        <?php endif; ?>
      </div>
      <div class="flex-grow-1">
        <h2 class="fw-bold mb-1"><?= htmlspecialchars($user['name']) ?></h2>
        <p class="mb-0 opacity-75"><i class="bi bi-envelope me-1"></i> <?= htmlspecialchars($user['email']) ?></p>
        <?php if (!empty($user['google_id'])): ?>
          <span class="badge bg-white text-success mt-2"><i class="bi bi-google me-1"></i> Connected with Google</span>
        <?php endif; ?>
      </div>
      <a href="logout.php" class="btn btn-light rounded-pill px-4 fw-semibold">Log Out</a>
    </div>

    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="stat-card">
          <h3 class="fw-bold text-success mb-0"><?= count($courses) ?></h3>
          <p class="text-muted mb-0">Courses Enrolled</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <h3 class="fw-bold text-success mb-0"><?= count($otherItems) ?></h3>
          <p class="text-muted mb-0">Other Purchases</p>
        </div>
      </div>
      <div class="col-md-4">
        <div class="stat-card">
          <h3 class="fw-bold text-success mb-0"><?= count($purchases) ?></h3>
          <p class="text-muted mb-0">Total Orders</p>
        </div>
      </div>
    </div>

    <h4 class="fw-bold mb-3">My Enrolled Courses</h4>
    <?php if (empty($courses)): ?>
      <div class="empty-state">
        <i class="bi bi-mortarboard" style="font-size: 3rem; opacity: 0.4;"></i>
        <p class="mt-3 mb-3">You haven't enrolled in any courses yet.</p>
        <a href="courses.php" class="btn btn-success rounded-pill px-4" style="background:var(--study-green); border:none;">Browse Courses</a>
      </div>
    <?php else: ?>
      <?php foreach ($courses as $c): ?>
        <div class="course-card">
          <div>
            <h5 class="fw-bold mb-1"><?= htmlspecialchars($c['service']) ?></h5>
            <small class="text-muted">Enrolled on <?= htmlspecialchars(date('M d, Y', strtotime($c['created_at']))) ?></small>
          </div>
          <a href="study.php?course_id=<?= (int) $c['id'] ?>" class="btn btn-success rounded-pill px-4" style="background:var(--study-green); border:none;">
            <i class="bi bi-play-circle-fill me-1"></i> Continue Learning
          </a>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

    <?php if (!empty($otherItems)): ?>
      <h4 class="fw-bold mb-3 mt-5">Other Purchases</h4>
      <?php foreach ($otherItems as $o): ?>
        <div class="course-card">
          <div>
            <h5 class="fw-bold mb-1"><?= htmlspecialchars($o['service']) ?></h5>
            <small class="text-muted">Purchased on <?= htmlspecialchars(date('M d, Y', strtotime($o['created_at']))) ?></small>
          </div>
          <?php if (!empty($o['file_path'])): ?>
            <a href="download.php?file=<?= urlencode($o['file_path']) ?>" class="btn btn-dark rounded-pill px-4">
              <i class="bi bi-cloud-arrow-down-fill me-1"></i> Download
            </a>
          <?php else: ?>
            <span class="badge bg-light text-secondary border px-3 py-2">Physical/Service</span>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>

  </main>

  <footer class="text-center py-4 text-muted small">
    <p>© 2026 SmartStudyPro - Matugga, Uganda</p>
  </footer>

</body>
</html>