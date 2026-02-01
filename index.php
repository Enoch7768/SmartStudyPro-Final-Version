<?php 
require_once 'cms-init.php'; 

$home = null;
$courses = [];
$contact = null;

try {
    if (function_exists('cockpit')) {
        $home = cockpit('content')->item('HomePage'); 
        $courses = cockpit('content')->items('Courses');
        // Fetch contact details so the footer is dynamic too
        $contact = cockpit('content')->item('ContactDetails');
    }
} catch (Exception $e) {
    $home = null; 
    $courses = [];
    $contact = null;
}

// Global Contact Fallbacks
$phone   = $contact['phone'] ?? '+256 704 416250';
$email   = $contact['email'] ?? 'smartstudypro36@gmail.com';
$address = strip_tags($contact['address'] ?? 'Kampala, Uganda');

$seoTitle       = $home['SEO-Title'] ?? $home['Title'] ?? 'SmartStudyPro | Leading Online Learning Platform in Uganda';
$seoDescription = $home['SEO-Description'] ?? 'Empowering students with innovative learning solutions, professional courses, and academic resources in Uganda.';
$siteUrl        = "https://smartstudypro.com"; // Replace with your actual domain
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  
  <title><?= htmlspecialchars($seoTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta name="keywords" content="online courses Uganda, e-learning, SmartStudyPro, education, professional training">
  <meta name="author" content="SmartStudyPro">

  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= $siteUrl ?>">
  <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta property="og:image" content="<?= $siteUrl ?>/Smart_Study_Logo_Fin-removebg-preview.png">

  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:url" content="<?= $siteUrl ?>">
  <meta property="twitter:title" content="<?= htmlspecialchars($seoTitle) ?>">
  <meta property="twitter:description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta property="twitter:image" content="<?= $siteUrl ?>/Smart_Study_Logo_Fin-removebg-preview.png">

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "EducationalOrganization",
    "name": "SmartStudyPro",
    "url": "<?= $siteUrl ?>",
    "logo": "<?= $siteUrl ?>/Smart_Study_Logo_Fin-removebg-preview.png",
    "contactPoint": {
      "@type": "ContactPoint",
      "telephone": "<?= $phone ?>",
      "contactType": "customer service",
      "email": "<?= $email ?>"
    },
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "Kampala",
      "addressCountry": "UG"
    }
  }
  </script>

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

<body class="index-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="SmartStudyPro">
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php" class="active">Home</a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="courses.php">Courses</a></li>
          <li><a href="contact.php">Contact</a></li>
          <li><a href="products.php">Products</a></li>
          <li><a href="cart.php" title="Shopping Cart"><i class="bi bi-bag"></i></a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>
      <a class="btn-getstarted" href="courses.php">Get Started</a>
    </div>
  </header>

  <main class="main">

    <section id="hero" class="hero section dark-background">
      <?php 
        $hImgData = $home['Hero-Image'] ?? $home['Image'] ?? null;
        $heroImg = !empty($hImgData['path']) 
                   ? '/schoolprojectt/SmartStudyProV2.3/cms/storage/uploads'.$hImgData['path'] 
                   : 'assets/img/IMG-20241120-WA0018.jpg';
      ?>
      <img src="<?= $heroImg ?>" alt="" data-aos="fade-in">

      <div class="container">
        <h2 data-aos="fade-up" data-aos-delay="100">
            <?= $home['Hero-Title'] ?? $home['Title'] ?? 'Learning Today,<br>Leading Tomorrow' ?>
        </h2>
        <p data-aos="fade-up" data-aos-delay="200">
            <?= $home['Hero-Subtitle'] ?? $home['Description'] ?? 'Empowering students with innovative learning solutions.' ?>
        </p>
        <div class="d-flex mt-4" data-aos="fade-up" data-aos-delay="300">
          <a href="courses.php" class="btn-get-started">Get Started</a>
        </div>
      </div>
    </section>

    <section id="about" class="about section">
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-6 order-1 order-lg-2" data-aos="fade-up" data-aos-delay="100">
            <img src="assets/img/20240102_164438.jpg" class="img-fluid" alt="">
          </div>
          <div class="col-lg-6 order-2 order-lg-1 content" data-aos="fade-up" data-aos-delay="200">
            <h3>About SmartStudyPro</h3>
            <p class="fst-italic">
              SmartStudyPro is a complete online learning platform designed to deliver education, resources, and bookings through one simple, secure website.
            </p>
            <ul>
              <li><i class="bi bi-check-circle"></i> <span>Clean, intuitive interface for distraction-free learning.</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Unified platform for content, bookings, and management.</span></li>
              <li><i class="bi bi-check-circle"></i> <span>Built on a strong foundation of reliability and security.</span></li>
            </ul>
            <a href="about.php" class="read-more"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </section>

    <section id="courses" class="courses section">
      <div class="container section-title" data-aos="fade-up">
        <h2>Courses</h2>
        <p>Popular Courses</p>
      </div>

      <div class="container">
        <div class="row">
          <?php if (!empty($courses)): ?>
            <?php foreach(array_slice($courses, 0, 3) as $course): // Show only top 3 on home ?>
              <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in">
                <div class="course-item">
                  <?php 
                    $cImgData = $course['Image'] ?? $course['image'] ?? null;
                    $cImg = !empty($cImgData['path']) ? '/schoolprojectt/SmartStudyProV2.3/cms/storage/uploads'.$cImgData['path'] : 'assets/img/course-1.jpg'; 
                  ?>
                  <img src="<?= $cImg ?>" class="img-fluid" alt="...">
                  <div class="course-content">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <p class="category"><?= htmlspecialchars($course['Category'] ?? 'General') ?></p>
                      <p class="price">UGX <?= number_format(floatval($course['Price'] ?? 0), 2) ?></p>
                    </div>
                    <h3><a href="course-details.php?id=<?= $course['_id'] ?>"><?= htmlspecialchars($course['Title'] ?? 'Untitled') ?></a></h3>
                    <div class="description"><?= strip_tags($course['Description'] ?? $course['description'] ?? '') ?></div>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
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