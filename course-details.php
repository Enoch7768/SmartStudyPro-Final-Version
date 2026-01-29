<?php 
require_once 'cms-init.php'; 

$course = null;
$contact = null;
$courseId = $_GET['id'] ?? null;

try {
    if (function_exists('cockpit')) {
        // Fetch specific course data
        if ($courseId) {
            $course = cockpit('content')->item('Courses', ['_id' => $courseId]);
        }
        // Fetch contact details for the footer
        $contact = cockpit('content')->item('ContactDetails');
    }
} catch (Exception $e) {
    $course = null;
    $contact = null;
}

// Fallback logic for Course Data
$displayTitle    = $course['Title'] ?? 'Course Details';
$displayPrice    = $course['Price'] ?? '0';
$displaySubtitle = $course['Subtitle'] ?? 'Master your skills with Expert Instructors | Comprehensive Training for All Levels';

// Dynamic Footer Fallbacks
$phone   = $contact['phone'] ?? '+256 704 416250';
$email   = $contact['email'] ?? 'smartstudypro36@gmail.com';
$address = strip_tags($contact['address'] ?? 'Kampala, Uganda');
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title><?= htmlspecialchars($displayTitle) ?> - SmartStudyPro</title>
  
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap" rel="stylesheet">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="starter-page-page">

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
          <li><a href="contact.php">Contact</a></li>
          <li><a href="cart.php" title="Shopping Cart"><i class="bi bi-bag"></i></a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
    </div>
  </header>

  <main class="main">

    <div class="page-title" data-aos="fade">
      <div class="heading">
        <div class="container">
          <div class="row d-flex justify-content-center text-center">
            <div class="col-lg-8">
              <h1><?= htmlspecialchars($displayTitle) ?></h1>
              <p class="mb-0"><?= htmlspecialchars($displaySubtitle) ?></p>
            </div>
          </div>
        </div>
      </div>
      <nav class="breadcrumbs">
        <div class="container">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li><a href="courses.php">Courses</a></li>
            <li class="current">Details</li>
          </ol>
        </div>
      </nav>
    </div>

    <section id="starter-section" class="starter-section section">
      <div class="container section-title" data-aos="fade-up">
        <h2><?= htmlspecialchars($displayTitle) ?></h2>
        <p>Book Your Session</p>
      </div>

      <div class="container" data-aos="fade-up">
        <div class="row justify-content-center">
          <div class="col-lg-8">
            <h3 class="text-center mb-4">Reservation Form</h3>
            <form action="booking.php" method="post" class="php-email-form">
                <input type="hidden" name="price" value="$<?= htmlspecialchars($displayPrice) ?>">
                <input type="hidden" name="service" value="<?= htmlspecialchars($displayTitle) ?>">
                
                <div class="row">
                    <div class="col-md-6 form-group">
                        <label for="name" class="mb-2">Full Name</label>
                        <input type="text" name="name" class="form-control" id="name" placeholder="John Doe" required>
                    </div>
                    <div class="col-md-6 form-group mt-3 mt-md-0">
                        <label for="email" class="mb-2">Email Address</label>
                        <input type="email" name="email" class="form-control" id="email" placeholder="example@mail.com" required>
                    </div>
                </div>
                <div class="form-group mt-3">
                    <label for="phone" class="mb-2">Phone Number</label>
                    <input type="text" name="phone" class="form-control" id="phone" placeholder="+256..." required>
                </div>
                <div class="form-group mt-3">
                    <label for="date" class="mb-2">Preferred Starting Date</label>
                    <input type="date" name="date" class="form-control" id="date" required>
                </div>
                <div class="form-group mt-3">
                    <label for="message" class="mb-2">Additional Notes</label>
                    <textarea name="message" class="form-control" id="message" rows="5" placeholder="Tell us about your learning goals..."></textarea>
                </div>
                <div class="form-group mt-3 text-center">
                    <div class="p-3 bg-light rounded">
                        <p class="mb-0"><strong>Total Course Fee:</strong> <span class="text-success" style="font-size: 1.2rem;">$<?= htmlspecialchars($displayPrice) ?></span></p>
                    </div>
                </div>
                <div class="text-center mt-4">
                    <button type="submit" class="btn btn-success" style="background-color: #5fcf80; border: none; padding: 12px 40px; border-radius: 50px; font-weight: 600;">Confirm Booking</button>
                </div>
            </form>
          </div>
        </div>
      </div>
    </section>

  </main>

  <footer id="footer" class="footer position-relative light-background">
    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.php" class="logo d-flex align-items-center">
            <span class="sitename">SmartStudyPro</span>
          </a>
          <div class="footer-contact pt-3">
            <p><?= htmlspecialchars($address) ?></p>
            <p class="mt-3"><strong>Phone:</strong> <span><?= htmlspecialchars($phone) ?></span></p>
            <p><strong>Email:</strong> <span><?= htmlspecialchars($email) ?></span></p>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <div id="preloader"></div>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/js/main.js"></script>

</body>
</html>