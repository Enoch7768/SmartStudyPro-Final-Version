<?php
// Set user_id cookie if not already set
if(!isset($_COOKIE['user_id'])) {
    $user_id = bin2hex(random_bytes(16));
    setcookie('user_id', $user_id, time() + (86400*30), "/"); // 30 days
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
        $db_file = __DIR__ . "/database/bookings.db";
        if (!file_exists(dirname($db_file))) mkdir(dirname($db_file), 0777, true);

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

 <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">

      <a href="index.html" class="logo d-flex align-items-center me-auto">
        <!-- Uncomment the line below if you also wish to use an image logo -->
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="SmartStudyPro Logo">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.html">Home<br></a></li>
          <li><a href="about.html">About</a></li>
          <li><a href="courses.html">Courses</a></li>
          <li><a href="contact.html">Contact</a></li>
          <li><a href="cart.php" title="Shopping Cart"><i class="bi bi-bag"></i></a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>


    </div>
  </header>

<main class="container py-5">
<?php if(isset($success) && $success): ?>
<div class="alert alert-success text-center">
    <h2>Booking Successful!</h2>
    <p>Thank you, <?= htmlspecialchars($name) ?>. Your booking for <strong><?= htmlspecialchars($service) ?></strong> on <?= htmlspecialchars($date) ?> has been received.</p>
    <p>Price: <?= htmlspecialchars($price) ?></p>
    <a href="cart.php" class="btn btn-primary">View Cart / Checkout</a>
</div>
<?php elseif(isset($error)): ?>
<div class="alert alert-danger text-center">
    <h2>Booking Failed</h2>
    <p><?= htmlspecialchars($error) ?></p>
    <a href="javascript:history.back()" class="btn btn-warning">Try Again</a>
</div>
<?php endif; ?>
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
            <li><a href="index.html">Home</a></li>
            <li><a href="about.html">About us</a></li>
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
