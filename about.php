<?php 
require_once 'cms-init.php'; 

$about = null;
$contact = null;

try {
    if (function_exists('cockpit')) {
        $about = cockpit('content')->item('AboutPage'); 
        $contact = cockpit('content')->item('ContactDetails');
    }
} catch (Exception $e) {
    $about = null; 
    $contact = null;
}

$displayTitle = $about['Main-Title'] ?? 'About Us';
$displayDesc  = $about['Story-Text'] ?? 'SmartStudyPro is dedicated to providing high-quality educational resources.';

$aboutImgData = $about['About-Image'] ?? null;
$aboutImg = !empty($aboutImgData['path']) 
            ? '/schoolprojectt/SmartStudyProV2.3/cms/storage/uploads'.$aboutImgData['path'] 
            : 'assets/img/about.jpg';

$phone   = $contact['phone'] ?? '+256 704 416250';
$email   = $contact['email'] ?? 'smartstudypro36@gmail.com';
$address = strip_tags($contact['address'] ?? 'Kampala, Uganda');

$seoTitle       = $about['SEO-Title'] ?? 'About Us | SmartStudyPro Uganda';
$seoDescription = $about['SEO-Description'] ?? strip_tags($displayDesc);
$siteUrl        = "https://smartstudypro.com";
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  
  <title><?= htmlspecialchars($seoTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
  
  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= $siteUrl ?>/about.php">
  <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta property="og:image" content="<?= $siteUrl . $aboutImg ?>">

  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:title" content="<?= htmlspecialchars($seoTitle) ?>">
  <meta property="twitter:description" content="<?= htmlspecialchars($seoDescription) ?>">

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

<body class="about-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="SmartStudyPro Logo">
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php" class="active">About</a></li>
          <li><a href="courses.php">Courses</a></li>
          <li><a href="contact.php">Contact</a></li>
          <li><a href="products.php">Products</a></li>
          <li><a href="cart.php"><i class="bi bi-bag"></i></a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
      <a class="btn-getstarted" href="courses.php">Get Started</a>
    </div>
  </header>

  <main class="main">

    <div class="page-title" data-aos="fade">
      <div class="heading">
        <div class="container">
          <div class="row d-flex justify-content-center text-center">
            <div class="col-lg-8">
              <h1>About Us</h1>
              <p class="mb-0">Empowering students through innovative learning.</p>
            </div>
          </div>
        </div>
      </div>
    </div>

    <section id="about-us" class="about-us section">
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 order-1 order-lg-2" data-aos="fade-up" data-aos-delay="100">
            <img src="<?= $aboutImg ?>" class="img-fluid" alt="About Image">
          </div>
          <div class="col-lg-6 order-2 order-lg-1 content" data-aos="fade-up" data-aos-delay="200">
            <h3><?= htmlspecialchars($displayTitle) ?></h3>
            <div class="fst-italic">
                <?= $displayDesc ?>
            </div>
            <ul class="mt-3">
              <li><i class="bi bi-check-circle"></i> <span>Expert instructors with years of experience.</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Tailored learning packages for every student.</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Innovative science and ICT project guidance.</span></li>
            </ul>
          </div>
        </div>
      </div>
    </section>

  </main>

  <footer id="footer" class="footer position-relative light-background">

    <div class="container footer-top">
      <div class="row gy-4">
        <div class="col-lg-4 col-md-6 footer-about">
          <a href="index.html" class="logo d-flex align-items-center">
            <span class="sitename">SmartStudyPro</span>
          </a>
            <div class="footer-contact pt-3">
            <p><?= htmlspecialchars($address) ?></p>
            <p class="mt-3"><strong>Phone:</strong> <span><?= htmlspecialchars($phone) ?></span></p>
            <p><strong>Email:</strong> <span><?= htmlspecialchars($email) ?></span></p>
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
            <li><a href="courses.php">Courses</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li><a href="products.php">Product</a></li>
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


         <div class="container text-center">
        <p>© 2026 SmartStudyPro. Empowering Education in Uganda.</p>
     </div>
     
  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>
  <div id="preloader"></div>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/js/main.js"></script>

</body>
</html>