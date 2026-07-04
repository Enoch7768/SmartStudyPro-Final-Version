<?php

$bookings = [];

if (isset($_COOKIE['user_id'])) {
    $user_id = $_COOKIE['user_id'];
    $db_file = __DIR__ . "/database/bookings.db";

    if (file_exists($db_file)) {
        try {
            $db = new PDO("sqlite:$db_file");
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $db->prepare("SELECT * FROM bookings WHERE user_id=:user_id AND paid=0");
            $stmt->execute([':user_id'=>$user_id]);
            $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log("cart.php DB error: " . $e->getMessage());
            $bookings = [];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Cart - SmartStudyPro</title>
<link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,8599&family=Raleway:ital,wght@1.2.3.4.5.6.7.8.9&display=swap" rel="stylesheet">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <link href="assets/css/main.css" rel="stylesheet">
</head>
<body>

 <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.html" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="SmartStudyPro Logo">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home<br></a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="courses.php">Courses</a></li>
          <li><a href="contact.php">Contact</a></li>
          <li><a href="products.php">Products</a></li>
          <li><a href="cart.php" title="Shopping Cart" class="active"><i class="bi bi-bag"></i></a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>


    </div>
  </header>

<main class="container py-5">
<h1 class="text-center mb-4">Your Cart</h1>

<?php if(empty($bookings)): ?>
<p class="text-center">Your cart is empty. <a href="courses.php">Book a session</a>.</p>
<?php else: ?>
<table class="table table-striped">
<thead>
<tr><th>Service</th><th>Date</th><th>Price</th><th>Action</th></tr>
</thead>
<tbody>
<?php 
$total = 0;
foreach($bookings as $b):
$price = floatval(str_replace('UGX ','',$b['price']));
$total += $price;
?>
<tr>
<td><?= htmlspecialchars($b['service']) ?></td>
<td><?= htmlspecialchars($b['date']) ?></td>
<td>UGX <?= number_format($price,2) ?></td>
<td><a href="remove_from_cart.php?id=<?= (int) $b['id'] ?>" class="btn btn-sm btn-danger">Remove</a></td>
</tr>
<?php endforeach; ?>
<tr>
<th colspan="2">Total</th>
<th colspan="2">UGX <?= number_format($total,2) ?></th>
</tr>
</tbody>
</table>
<div class="text-center mt-4">
<a href="checkout.php" class="btn btn-primary btn-lg">Proceed to Checkout</a>
</div>
<?php endif; ?>
</main>
  <footer class="text-center mt-5 text-muted small">
    <p>© 2026 SmartStudyPro - Matugga, Uganda</p>
  </footer>
</body>
</html>