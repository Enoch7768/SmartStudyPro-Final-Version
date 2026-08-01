<?php 
require_once 'cms-init.php'; 

$home = null;
$courses = [];
$contact = null;

try {
    if (function_exists('cockpit')) {
        $home = cockpit('content')->item('HomePage'); 
        $courses = cockpit('content')->items('Courses');
        $contact = cockpit('content')->item('ContactDetails');
    }
} catch (Exception $e) {
    $home = null; 
    $courses = [];
    $contact = null;
}

$phone   = $contact['phone'] ?? '+256 704 416250';
$email   = $contact['email'] ?? 'smartstudypro36@gmail.com';
$address = strip_tags($contact['address'] ?? 'Kampala, Uganda');

$seoTitle       = $home['SEO-Title'] ?? $home['Title'] ?? 'SmartStudyPro | Study Made Simple, Success Made Sure';
$seoDescription = $home['SEO-Description'] ?? 'Empowering students in Uganda with innovative learning solutions, professional courses, and academic guidance.';
$siteUrl        = "https://smartstudypro.com"; 
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
  <title><?= htmlspecialchars($seoTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta name="keywords" content="online courses Uganda, e-learning, SmartStudyPro, education, tutoring Kampala">
  <meta name="author" content="SmartStudyPro">

  <meta property="og:type" content="website">
  <meta property="og:url" content="<?= $siteUrl ?>">
  <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta property="og:image" content="<?= $siteUrl ?>/Smart_Study_Logo_Fin-removebg-preview.png">

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "EducationalOrganization",
    "name": "SmartStudyPro",
    "url": "<?= $siteUrl ?>",
    "logo": "<?= $siteUrl ?>/Smart_Study_Logo_Fin-removebg-preview.png",
    "slogan": "Study Made Simple, Success Made Sure",
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

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;500;600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <style>
    :root {
      --ssp-navy: #0C086B;
      --ssp-navy-dark: #070443;
      --ssp-orange: #FF7A00;
      --ssp-orange-hover: #E06B00;
      --ssp-bg-soft: #F8FAFC;
      --ssp-text-main: #1E293B;
      --ssp-text-muted: #64748B;
    }

    body {
      font-family: 'Plus Jakarta Sans', sans-serif;
      color: var(--ssp-text-main);
      background-color: #FFFFFF;
    }

    h1, h2, h3, h4, h5, .brand-font {
      font-family: 'Outfit', sans-serif;
    }

    .ssp-header {
      background: rgba(255, 255, 255, 0.95);
      backdrop-filter: blur(12px);
      border-bottom: 2px solid rgba(12, 8, 107, 0.08);
      transition: all 0.3s ease;
    }

    .navmenu ul {
      list-style: none !important;
      margin: 0 !important;
      padding: 0 !important;
      display: flex !important;
      align-items: center !important;
      gap: 24px;
    }

    .navmenu ul li {
      list-style: none !important;
      margin: 0 !important;
      padding: 0 !important;
    }

    .navmenu ul li a {
      color: var(--ssp-navy);
      font-weight: 600;
      font-size: 0.95rem;
      text-decoration: none !important;
      display: inline-block;
      white-space: nowrap;
      transition: color 0.2s ease;
    }

    .navmenu ul li a:hover,
    .navmenu ul li a.active {
      color: var(--ssp-orange) !important;
      font-weight: 700;
    }

    .dropdown-toggle-no-caret::after {
      display: none !important;
    }

    .dropdown-menu .dropdown-item:hover {
      background-color: var(--ssp-bg-soft);
      color: var(--ssp-orange);
    }

    .btn-ssp-primary {
      background-color: var(--ssp-orange);
      color: #FFFFFF;
      font-weight: 700;
      border-radius: 10px;
      padding: 10px 24px;
      border: none;
      box-shadow: 0 4px 14px rgba(255, 122, 0, 0.35);
      transition: all 0.25s ease;
    }

    .btn-ssp-primary:hover {
      background-color: var(--ssp-orange-hover);
      color: #FFFFFF;
      transform: translateY(-2px);
      box-shadow: 0 6px 20px rgba(255, 122, 0, 0.45);
    }

    .btn-ssp-navy {
      background-color: var(--ssp-navy);
      color: #FFFFFF;
      font-weight: 600;
      border-radius: 10px;
      padding: 10px 24px;
      border: none;
      transition: all 0.25s ease;
    }

    .btn-ssp-navy:hover {
      background-color: var(--ssp-navy-dark);
      color: #FFFFFF;
      transform: translateY(-2px);
    }

    .ssp-hero {
      position: relative;
      padding: 140px 0 100px;
      background-size: cover;
      background-position: center;
      background-repeat: no-repeat;
    }

    .ssp-hero::before {
      content: "";
      position: absolute;
      inset: 0;
      background: linear-gradient(135deg, rgba(12, 8, 107, 0.92) 0%, rgba(7, 4, 67, 0.88) 100%);
      z-index: 1;
    }

    .ssp-hero .container {
      position: relative;
      z-index: 2;
    }

    .hero-tagline {
      display: inline-block;
      color: var(--ssp-orange);
      font-weight: 700;
      letter-spacing: 1px;
      text-transform: uppercase;
      font-size: 0.875rem;
      background: rgba(255, 122, 0, 0.12);
      padding: 6px 16px;
      border-radius: 30px;
      border: 1px solid rgba(255, 122, 0, 0.3);
    }

    .pixel-decor {
      display: inline-flex;
      gap: 4px;
      margin-bottom: 12px;
    }

    .pixel-decor span {
      width: 8px;
      height: 8px;
      border-radius: 2px;
    }

    .pixel-decor .px-1 { background-color: var(--ssp-orange); }
    .pixel-decor .px-2 { background-color: #3B82F6; }
    .pixel-decor .px-3 { background-color: #FFFFFF; }

    .ssp-course-card {
      border: 1px solid #E2E8F0;
      border-radius: 16px;
      overflow: hidden;
      background: #FFFFFF;
      box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
      transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .ssp-course-card:hover {
      transform: translateY(-8px);
      border-color: rgba(255, 122, 0, 0.4);
      box-shadow: 0 20px 25px -5px rgba(12, 8, 107, 0.1);
    }

    .ssp-card-badge {
      background: #EFF6FF;
      color: var(--ssp-navy);
      font-size: 0.75rem;
      font-weight: 700;
      padding: 4px 12px;
      border-radius: 20px;
      text-transform: uppercase;
    }

    .ssp-price-tag {
      color: var(--ssp-orange);
      font-weight: 800;
      font-size: 1.15rem;
    }

    .ssp-footer {
      background-color: var(--ssp-navy-dark);
      color: #94A3B8;
    }

    .ssp-footer-brand {
      color: #FFFFFF;
      font-size: 1.5rem;
      font-weight: 800;
    }

    .ssp-footer-brand span {
      color: var(--ssp-orange);
    }

    .social-icon-btn {
      width: 38px;
      height: 38px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.08);
      color: #FFFFFF;
      transition: all 0.2s ease;
      text-decoration: none;
    }

    .social-icon-btn:hover {
      background: var(--ssp-orange);
      color: #FFFFFF;
    }
  </style>
</head>

<body class="index-page">

  <header id="header" class="header ssp-header d-flex align-items-center sticky-top py-2">
    <div class="container-fluid container-xl position-relative d-flex align-items-center justify-content-between">
      
      <a href="index.php" class="logo d-flex align-items-center me-auto me-xl-0 text-decoration-none">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="SmartStudyPro Logo" height="48">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'index.php') ? 'active' : '' ?>">Home</a></li>
          <li><a href="about.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'about.php') ? 'active' : '' ?>">About Us</a></li>
          <li><a href="courses.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'courses.php') ? 'active' : '' ?>">Courses</a></li>
          <li><a href="products.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'products.php') ? 'active' : '' ?>">Products</a></li>
          <li><a href="contact.php" class="<?= (basename($_SERVER['PHP_SELF']) == 'contact.php') ? 'active' : '' ?>">Contact</a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list fs-2 ms-3"></i>
      </nav>

        <div class="dropdown">
          <a href="#" class="text-dark fs-5 text-decoration-none dropdown-toggle-no-caret" id="userMenuDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="Account">
            <i class="bi bi-person-circle"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 mt-2" aria-labelledby="userMenuDropdown">
            <li><a class="dropdown-item py-2" href="profile.php"><i class="bi bi-person me-2" style="color: var(--ssp-navy);"></i>My Profile</a></li>
            <li><a class="dropdown-item py-2" href="cart.php"><i class="bi bi-bag me-2" style="color: var(--ssp-navy);"></i>My Cart</a></li>
            <li><hr class="dropdown-divider"></li>
          </ul>
        </div>

        <a class="btn-ssp-primary d-none d-sm-inline-block text-decoration-none ms-2" href="courses.php">Explore Courses</a>
      </div>

    </div>
  </header>

  <main class="main">

    <?php 
      $hImgData = $home['Hero-Image'] ?? $home['Image'] ?? null;
      $heroImg = !empty($hImgData['path']) 
                 ? '/schoolprojectt/SmartStudyProV2.3/cms/storage/uploads'.$hImgData['path'] 
                 : 'assets/img/IMG-20241120-WA0018.jpg';
    ?>
    <section id="hero" class="ssp-hero" style="background-image: url('<?= $heroImg ?>');">
      <div class="container">
        <div class="row align-items-center">
          <div class="col-lg-8 text-center text-lg-start">
            
            <div class="pixel-decor" data-aos="fade-down">
              <span class="px-1"></span>
              <span class="px-2"></span>
              <span class="px-3"></span>
            </div>

            <div class="mb-3" data-aos="fade-up">
              <span class="hero-tagline">Study Made Simple, Success Made Sure</span>
            </div>

            <h1 class="display-4 fw-bold text-white mb-3" data-aos="fade-up" data-aos-delay="100">
              <?= $home['Hero-Title'] ?? $home['Title'] ?? 'Learning Today,<br>Leading Tomorrow' ?>
            </h1>

            <p class="lead text-light opacity-90 mb-4" data-aos="fade-up" data-aos-delay="200">
              <?= $home['Hero-Subtitle'] ?? $home['Description'] ?? 'Empowering students with innovative learning solutions, professional tutoring, and academic excellence in Uganda.' ?>
            </p>

            <div class="d-flex flex-wrap gap-3 justify-content-center justify-content-lg-start" data-aos="fade-up" data-aos-delay="300">
              <a href="courses.php" class="btn-ssp-primary text-decoration-none">Start Learning Today</a>
              <a href="about.php" class="btn btn-outline-light rounded-3 px-4 py-2 fw-semibold text-decoration-none">Discover SmartStudyPro</a>
            </div>

          </div>
        </div>
      </div>
    </section>

    <section id="about" class="about section py-5">
      <div class="container" data-aos="fade-up">
        <div class="row gy-4 align-items-center">
          <div class="col-lg-6 order-1 order-lg-2">
            <div class="position-relative">
              <img src="assets/img/20240102_164438.jpg" class="img-fluid rounded-4 shadow-lg" alt="SmartStudyPro Learning Environment">
            </div>
          </div>

          <div class="col-lg-6 order-2 order-lg-1">
            <span class="text-uppercase fw-bold text-primary fs-7 tracking-wider">About SmartStudyPro</span>
            <h2 class="fw-bold mt-1 mb-3" style="color: var(--ssp-navy);">Empowering Education Through Smart & Flexible Learning</h2>
            <p class="text-muted">
              SmartStudyPro is a complete online educational platform engineered to simplify learning, resource access, and academic booking across Uganda.
            </p>

            <div class="my-4">
              <div class="d-flex gap-3 mb-3">
                <div class="text-warning fs-4"><i class="bi bi-patch-check-fill" style="color: var(--ssp-orange);"></i></div>
                <div>
                  <h6 class="fw-bold mb-1" style="color: var(--ssp-navy);">Distraction-Free Platform</h6>
                  <p class="text-muted small mb-0">Clean, intuitive layout structured specifically to keep students focused on academic success.</p>
                </div>
              </div>

              <div class="d-flex gap-3 mb-3">
                <div class="text-warning fs-4"><i class="bi bi-patch-check-fill" style="color: var(--ssp-orange);"></i></div>
                <div>
                  <h6 class="fw-bold mb-1" style="color: var(--ssp-navy);">Unified Learning Ecosystem</h6>
                  <p class="text-muted small mb-0">Seamless access to course content, holiday guidance packages, and specialized ICT lessons.</p>
                </div>
              </div>

              <div class="d-flex gap-3">
                <div class="text-warning fs-4"><i class="bi bi-patch-check-fill" style="color: var(--ssp-orange);"></i></div>
                <div>
                  <h6 class="fw-bold mb-1" style="color: var(--ssp-navy);">Reliable & Secured Infrastructure</h6>
                  <p class="text-muted small mb-0">Built on modern, fast web standards to ensure student data safety and fast dynamic loading.</p>
                </div>
              </div>
            </div>

            <a href="about.php" class="btn-ssp-navy text-decoration-none d-inline-block">Read More About Us <i class="bi bi-arrow-right ms-1"></i></a>
          </div>
        </div>
      </div>
    </section>

    <section id="courses" class="courses section py-5" style="background-color: var(--ssp-bg-soft);">
      <div class="container text-center mb-5" data-aos="fade-up">
        <span class="badge px-3 py-2 mb-2" style="background-color: rgba(12,8,107,0.1); color: var(--ssp-navy);">Featured Content</span>
        <h2 class="fw-bold" style="color: var(--ssp-navy);">Explore Popular Courses</h2>
        <p class="text-muted">Explore structured learning programs designed to boost academic growth.</p>
      </div>

      <div class="container">
        <div class="row g-4">
          <?php if (!empty($courses)): ?>
            <?php foreach(array_slice($courses, 0, 3) as $course): ?>
              <div class="col-lg-4 col-md-6 d-flex align-items-stretch" data-aos="zoom-in">
                <div class="ssp-course-card w-100 d-flex flex-column">
                  <?php 
                    $cImgData = $course['Image'] ?? $course['image'] ?? null;
                    $cImg = !empty($cImgData['path']) 
                            ? '/schoolprojectt/SmartStudyProV2.3/cms/storage/uploads'.$cImgData['path'] 
                            : 'assets/img/course-1.jpg'; 
                  ?>
                  <div class="position-relative">
                    <img src="<?= $cImg ?>" class="img-fluid w-100" style="height: 210px; object-fit: cover;" alt="<?= htmlspecialchars($course['Title'] ?? 'Course') ?>">
                  </div>

                  <div class="p-4 d-flex flex-column flex-grow-1">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <span class="ssp-card-badge"><?= htmlspecialchars($course['Category'] ?? 'General') ?></span>
                      <span class="ssp-price-tag">UGX <?= number_format(floatval($course['Price'] ?? 0)) ?></span>
                    </div>

                    <h3 class="h5 fw-bold mb-2">
                      <a href="course-details.php?id=<?= $course['_id'] ?>" class="text-decoration-none" style="color: var(--ssp-navy);">
                        <?= htmlspecialchars($course['Title'] ?? 'Untitled Course') ?>
                      </a>
                    </h3>

                    <p class="text-muted small mb-4 flex-grow-1">
                      <?= htmlspecialchars(substr(strip_tags($course['Description'] ?? $course['description'] ?? ''), 0, 110)) ?>...
                    </p>

                    <a href="course-details.php?id=<?= $course['_id'] ?>" class="btn btn-outline-primary w-100 mt-auto rounded-3 text-decoration-none" style="color: var(--ssp-navy); border-color: var(--ssp-navy);">
                      View Details
                    </a>
                  </div>
                </div>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <div class="text-center text-muted py-5">
              <p>No courses currently featured. Please check back shortly!</p>
            </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

  </main>

  <footer id="footer" class="ssp-footer pt-5 pb-3">
    <div class="container footer-top mb-4">
      <div class="row gy-4">
        
        <div class="col-lg-4 col-md-6">
          <a href="index.php" class="ssp-footer-brand text-decoration-none mb-3 d-inline-block">
            SmartStudy<span>Pro</span>
          </a>
          <p class="small text-light opacity-75 mb-3">Study Made Simple, Success Made Sure.</p>
          
          <div class="small mb-3">
            <p class="mb-1"><i class="bi bi-geo-alt-fill me-2" style="color: var(--ssp-orange);"></i><?= htmlspecialchars($address) ?></p>
            <p class="mb-1"><i class="bi bi-telephone-fill me-2" style="color: var(--ssp-orange);"></i><?= htmlspecialchars($phone) ?></p>
            <p class="mb-1"><i class="bi bi-envelope-fill me-2" style="color: var(--ssp-orange);"></i><?= htmlspecialchars($email) ?></p>
          </div>

          <div class="d-flex gap-2">
            <a href="#" class="social-icon-btn"><i class="bi bi-twitter-x"></i></a>
            <a href="#" class="social-icon-btn"><i class="bi bi-facebook"></i></a>
            <a href="#" class="social-icon-btn"><i class="bi bi-instagram"></i></a>
            <a href="#" class="social-icon-btn"><i class="bi bi-linkedin"></i></a>
          </div>
        </div>

        <div class="col-lg-3 col-md-3">
          <h5 class="text-white fw-bold mb-3">Quick Navigation</h5>
          <ul class="list-unstyled small">
            <li class="mb-2"><a href="index.php" class="text-decoration-none text-light opacity-75">Home</a></li>
            <li class="mb-2"><a href="about.php" class="text-decoration-none text-light opacity-75">About Us</a></li>
            <li class="mb-2"><a href="courses.php" class="text-decoration-none text-light opacity-75">Courses</a></li>
            <li class="mb-2"><a href="products.php" class="text-decoration-none text-light opacity-75">Products & Materials</a></li>
            <li class="mb-2"><a href="contact.php" class="text-decoration-none text-light opacity-75">Contact Us</a></li>
          </ul>
        </div>

        <div class="col-lg-5 col-md-3">
          <h5 class="text-white fw-bold mb-3">Core Educational Offerings</h5>
          <ul class="list-unstyled small opacity-75">
            <li class="mb-2"><i class="bi bi-chevron-right text-warning me-1"></i> Private & Customized Tutoring</li>
            <li class="mb-2"><i class="bi bi-chevron-right text-warning me-1"></i> Holiday Package & Guided Learning</li>
            <li class="mb-2"><i class="bi bi-chevron-right text-warning me-1"></i> Homework & Assignment Support</li>
            <li class="mb-2"><i class="bi bi-chevron-right text-warning me-1"></i> Science Project & STEM Innovation</li>
            <li class="mb-2"><i class="bi bi-chevron-right text-warning me-1"></i> Applied Computer & ICT Lessons</li>
          </ul>
        </div>

      </div>
    </div>

    <div class="container text-center border-top border-secondary pt-3 mt-3 opacity-75 small">
      <p class="mb-0">&copy; <?= date('Y') ?> <strong>SmartStudyPro</strong>. All Rights Reserved. Empowering Education in Uganda.</p>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center text-decoration-none"><i class="bi bi-arrow-up-short"></i></a>
  <div id="preloader"></div>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/js/main.js"></script>

</body>
</html>