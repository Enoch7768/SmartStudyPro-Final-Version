<?php
session_start();

// 1. Simple Password Protection
$admin_password = "test"; // Change this for deployment

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: admin.php");
    exit;
}

if (isset($_POST['login'])) {
    if ($_POST['password'] === $admin_password) {
        $_SESSION['admin_logged_in'] = true;
    } else {
        $login_error = "Invalid Password";
    }
}

// 2. Fetch Data if Logged In
$bookings = [];
if (isset($_SESSION['admin_logged_in'])) {
    try {
        $db = new PDO("sqlite:database/bookings.db");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Fetch latest bookings first
        $stmt = $db->query("SELECT * FROM bookings ORDER BY created_at DESC");
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        $db_error = $e->getMessage();
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

      <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
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

</body>
</html>