<?php
// Set user_id cookie if not already set
if(!isset($_COOKIE['user_id'])) {
    $user_id = bin2hex(random_bytes(16));
    setcookie('user_id', $user_id, time() + (86400 * 30), "/"); // 30 days
} else {
    $user_id = $_COOKIE['user_id'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'] ?? '';
    $email = $_POST['email'] ?? '';
    $phone = $_POST['phone'] ?? '';
    $date = $_POST['date'] ?? '';
    $service = $_POST['service'] ?? '';
    $message = $_POST['message'] ?? '';
    $price = $_POST['price'] ?? '';

    try {
        $db_dir = __DIR__ . "/database";
        $db_file = $db_dir . "/bookings.db";
        
        // Create directory with correct permissions if it doesn't exist
        if (!file_exists($db_dir)) {
            mkdir($db_dir, 0777, true);
        }

        $db = new PDO("sqlite:$db_file");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Create table if not exists
        $db->exec("CREATE TABLE IF NOT EXISTS bookings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id TEXT,
            name TEXT,
            email TEXT,
            phone TEXT,
            date TEXT,
            service TEXT,
            message TEXT,
            price TEXT,
            paid INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");

        $stmt = $db->prepare("INSERT INTO bookings (user_id, name, email, phone, date, service, message, price) 
                              VALUES (:user_id, :name, :email, :phone, :date, :service, :message, :price)");
        $stmt->execute([
            ':user_id' => $user_id,
            ':name' => $name,
            ':email' => $email,
            ':phone' => $phone,
            ':date' => $date,
            ':service' => $service,
            ':message' => $message,
            ':price' => $price
        ]);

        $success = true;
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Booking Received - SmartStudyPro</title>
  
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body>
  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="SmartStudyPro Logo">
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="courses.php">Courses</a></li>
          <li><a href="contact.html">Contact</a></li>
          <li><a href="cart.php" title="Shopping Cart" class="active"><i class="bi bi-bag"></i></a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
    </div>
  </header>

  <main class="main">
    <section class="section">
      <div class="container py-5" data-aos="fade-up">
        <?php if(isset($success) && $success): ?>
          <div class="card shadow p-5 text-center border-0">
            <div class="mb-4">
              <i class="bi bi-check-circle-fill text-success" style="font-size: 4rem;"></i>
            </div>
            <h2 class="fw-bold">Booking Successful!</h2>
            <p class="lead text-secondary">Thank you, <strong><?= htmlspecialchars($name) ?></strong>. Your session for <strong><?= htmlspecialchars($service) ?></strong> has been added to your cart.</p>
            <hr class="my-4">
            <div class="d-flex justify-content-center gap-3">
              <a href="courses.php" class="btn btn-outline-secondary px-4">Add More Courses</a>
              <a href="cart.php" class="btn btn-success px-4" style="background-color: #5fcf80; border: none;">View Cart & Checkout</a>
            </div>
          </div>
        <?php elseif(isset($error)): ?>
          <div class="alert alert-danger p-4 text-center">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <strong>Booking Failed:</strong> <?= htmlspecialchars($error) ?>
            <div class="mt-3">
              <a href="javascript:history.back()" class="btn btn-dark">Go Back</a>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </section>
  </main>

  <footer id="footer" class="footer light-background py-4 text-center border-top">
      <p>© <strong>SmartStudyPro</strong> - All Rights Reserved</p>
  </footer>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>
</html>