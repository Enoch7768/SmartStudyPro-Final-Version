<?php

require_once 'cms-init.php';
require_once 'auth.php';

if(!isset($_COOKIE['user_id'])) {
    $user_id = bin2hex(random_bytes(16));
    setcookie('user_id', $user_id, time() + (86400*30), "/"); 
} else {
    $user_id = $_COOKIE['user_id'];
}

$success = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name     = trim($_POST['name']    ?? '');
    $email    = trim($_POST['email']   ?? '');
    $phone    = trim($_POST['phone']   ?? 'N/A');
    $date     = $_POST['date']    ?? date('Y-m-d');
    $service  = trim($_POST['service'] ?? $_POST['item_name'] ?? 'Unknown Item');
    $message  = trim($_POST['message'] ?? $_POST['address'] ?? '');
    $price    = $_POST['price']   ?? $_POST['item_price'] ?? '0';
    $filePath = $_POST['product_file'] ?? '';
    if ($name === '' || $email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please provide a valid name and email address.";
    }
    $price = number_format((float) preg_replace('/[^0-9.]/', '', (string) $price), 2, '.', '');
    $filePath = ltrim(str_replace(['..', '\\'], '', (string) $filePath), '/');

    $courseId  = $_POST['course_id']  ?? null;
    $productId = $_POST['product_id'] ?? null;

    if (!isset($error) && $courseId && function_exists('cockpit')) {
        $course = cockpit('content')->item('Courses', ['_id' => $courseId]);
        if ($course) {
            $service  = $course['Title'] ?? $service;
            $price    = number_format((float) ($course['Price'] ?? 0), 2, '.', '');
            $filePath = ''; 
        } else {
            $error = "The selected course could not be found.";
        }
    } elseif (!isset($error) && $productId && function_exists('cockpit')) {
        $product = cockpit('content')->item('Products', ['_id' => $productId]);
        if ($product) {
            $service  = $product['Title'] ?? $service;
            $price    = number_format((float) ($product['Price'] ?? 0), 2, '.', '');
            $realFile = $product['ProductFile']['path'] ?? '';
            $filePath = $realFile ? ltrim(str_replace(['..', '\\'], '', (string) $realFile), '/') : '';
        } else {
            $error = "The selected product could not be found.";
        }
    }
    $accountId = null;
    if (function_exists('current_user') && ($user = current_user())) {
        $accountId = $user['id'];
        $name  = $user['name'];
        $email = $user['email'];
    }

    try {
        if (isset($error)) {
            throw new Exception($error);
        }

        $db_dir = __DIR__ . "/database";
        $db_file = $db_dir . "/bookings.db";
        
        if (!file_exists($db_dir)) mkdir($db_dir, 0777, true);

        $db = new PDO("sqlite:$db_file");
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        ensure_bookings_columns($db);
        $stmt = $db->prepare("INSERT INTO bookings (user_id, name, email, phone, date, service, message, price, file_path, account_id) 
                              VALUES (:user_id, :name, :email, :phone, :date, :service, :message, :price, :file_path, :account_id)");
        $stmt->execute([
            ':user_id'    => $user_id,
            ':name'       => $name,
            ':email'      => $email,
            ':phone'      => $phone,
            ':date'       => $date,
            ':service'    => $service,
            ':message'    => $message,
            ':price'      => $price,
            ':file_path'  => $filePath,
            ':account_id' => $accountId
        ]);

        $success = true;
    } catch (Exception $e) {
        if (!isset($error)) {
            error_log("booking.php error: " . $e->getMessage());
            $error = "Sorry, we couldn't process your order. Please try again.";
        }
    }
} else {
    header("Location: products.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Processing Order - SmartStudyPro</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link rel="shortcut icon" href="Smart_Study_Logo_Fin-removebg-preview.png" type="image/x-icon">
  <link rel="apple-touch-icon" href="Smart_Study_Logo_Fin-removebg-preview.png">
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="bg-light">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="Logo">
      </a>
    </div>
  </header>

  <main class="container py-5 mt-5">
    <div class="row justify-content-center">
      <div class="col-md-7">
        
        <?php if($success): ?>
          <div class="card shadow-lg border-0 text-center p-5 rounded-4">
            <div class="mb-4">
              <i class="bi bi-bag-check-fill text-success" style="font-size: 5rem;"></i>
            </div>
            <h2 class="fw-bold">Item Added to Cart</h2>
            <p class="text-muted px-lg-5">Your selection <strong>"<?= htmlspecialchars($service) ?>"</strong> has been successfully added to your shopping bag.</p>
            <hr class="my-4 mx-5 opacity-25">
            <div class="d-grid gap-2 d-md-block">
              <a href="products.php" class="btn btn-outline-secondary rounded-pill px-4 me-md-2">Continue Shopping</a>
              <a href="cart.php" class="btn btn-success rounded-pill px-4" style="background:#5fcf80; border:none;">View My Cart</a>
            </div>
          </div>

        <?php else: ?>
          <div class="card shadow-lg border-0 p-5 rounded-4 border-start border-danger border-5">
            <h3 class="text-danger fw-bold"><i class="bi bi-exclamation-triangle-fill"></i> Database Sync Required</h3>
            <p class="mt-3"><?= htmlspecialchars($error) ?></p>
            <p class="small text-muted">Technical Tip: If this error persists, try deleting <code>database/bookings.db</code> to reset the schema.</p>
            <a href="javascript:history.back()" class="btn btn-dark rounded-pill mt-3">Go Back</a>
          </div>
        <?php endif; ?>

      </div>
    </div>
  </main>

  <footer class="text-center mt-5 text-muted small">
    <p>© 2026 SmartStudyPro - Matugga, Uganda</p>
  </footer>

</body>
</html>