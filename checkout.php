<?php
if (!isset($_COOKIE['user_id'])) die("No user identified.");
$user_id = $_COOKIE['user_id'];

$db_file = __DIR__ . "/database/bookings.db";
if (!file_exists($db_file)) die("Database not found");

try {
    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("SELECT * FROM bookings WHERE user_id=:user_id AND paid=0");
    $stmt->execute([':user_id'=>$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if(empty($bookings)) die("No unpaid bookings found.");

    $total = 0;
    foreach($bookings as $b){
        $total += floatval(str_replace('$','',$b['price']));
    }
} catch(Exception $e){
    die("DB Error: ".$e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Checkout - SmartStudyPro</title>
<link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="apple-touch-icon">

  <!-- Fonts -->
  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;1,8599&family=Raleway:ital,wght@1.2.3.4.5.6.7.8.9&display=swap" rel="stylesheet">

  <!-- Vendor CSS Files -->
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <!-- Main CSS File -->
  <link href="assets/css/main.css" rel="stylesheet">
</head>
<body>

<header><!-- header code --></header>

<main class="container py-5">
<h1 class="text-center mb-4">Checkout</h1>

<h3>Order Summary</h3>
<ul class="list-group mb-4">
<?php foreach($bookings as $b): ?>
<li class="list-group-item d-flex justify-content-between">
<?= htmlspecialchars($b['service']) ?> - <?= htmlspecialchars($b['date']) ?>
<span><?= $b['price'] ?></span>
</li>
<?php endforeach; ?>
<li class="list-group-item d-flex justify-content-between fw-bold">
Total
<span>$<?= number_format($total,2) ?></span>
</li>
</ul>

<h3>Select Payment Method</h3>
<form action="process_payment.php" method="post">
<input type="hidden" name="user_id" value="<?= $user_id ?>">
<input type="hidden" name="total" value="<?= $total ?>">
<div class="mb-3">
<select name="payment_method" class="form-select" required>
<option value="">--Choose Payment Method--</option>
<option value="paypal">PayPal</option>
<option value="dpo">DPO Pay</option>
<option value="pay_at_booking">Pay at Booking</option>
</select>
</div>
<div class="text-center">
<button type="submit" class="btn btn-primary btn-lg">Pay / Confirm Booking</button>
</div>
</form>
</main>

<footer id="footer" class="footer position-relative light-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">SmartStudyPro</span>
          </a>
          <div class="footer-contact pt-3">
            <p>A108 Adam Street</p>
            <p>New York, NY 535022</p>
            <p class="mt-3"><strong>Phone:</strong> <span>+256 704 416250</span></p>
            <p><strong>Email:</strong> <span>smartstudypro36@gmail.com</span></p>
          </div>
          <div class="social-links d-flex mt-4">
            <a href=""><i class="bi bi-twitter-x"></i></a>
            <a href=""><i class="bi bi-facebook"></i></a>
            <a href=""><i class="bi bi-instagram"></i></a>
            <a href=""><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Useful Links</h4>
          <ul>
            <li><a href="index.php">Home</a></li>
            <li><a href="about.php">About us</a></li>
            <li><a href="#services">Services</a></li>
            <li><a href="#">Terms of service</a></li>
            <li><a href="#">Privacy policy</a></li>
          </ul>
        </div>

        <div class="col-lg-2 col-md-3 footer-links">
          <h4>Our Courses</h4>
          <ul>
            <li>Private Tutoring</li>
            <li>Holiday Package Guidance</li>
            <li>Homework Assistance</li>
            <li>Science Project Work Innovation</li>
            <li>Computer Lessons (ICT)</li>
          </ul>
        </div>

        <div class="col-lg-4 col-md-12 footer-newsletter">
          <h4>Our Newsletter</h4>
          <p>Subscribe to our newsletter and receive the latest news about our products and services!</p>
          <form action="forms/newsletter.php" method="post" class="php-email-form">
            <div class="newsletter-form"><input type="email" name="email" placeholder="Enter your email"><input type="submit" value="Subscribe"></div>
            <div class="loading">Loading</div>
            <div class="error-message"></div>
            <div class="sent-message">Your subscription request has been sent. Thank you!</div>
          </form>
        </div>

      </div>
    </div>

   <!-- <div display="hidden">
      <p>© <span>Copyright</span> <strong class="px-1 sitename">Mentor</strong> <span>All Rights Reserved</span></p>
      <div class="credits">
        <!-- All the links in the footer should remain intact. -->
        <!-- You can delete the links only if you've purchased the pro version. -->
        <!-- Licensing information: https://bootstrapmade.com/license/ -->
        <!-- Purchase the pro version with working PHP/AJAX contact form: [buy-url] -->
       <!-- Designed by <a href="https://bootstrapmade.com/">BootstrapMade</a> Distributed by <a href=“https://themewagon.com>ThemeWagon
      </div>
    </div>
-->
  </footer>
</body>
</html>
