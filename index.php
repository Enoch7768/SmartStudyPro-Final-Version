<?php
// 1. Load the ButterCMS config
require_once 'config.php';

try {
    // 2. Fetch data from ButterCMS using your specific slug
    $homepageResponse = $butterCms->fetchPage('home_page', 'smartstudypro-home');
    $fields = $homepageResponse->getFields();
} catch (Exception $e) {
    // Fallback data if the API fails
    $fields = [
        'hero_title' => 'Learning Today,<br>Leading Tomorrow',
        'hero_subtitle' => 'SmartStudyPro is dedicated to empowering students.',
        'hero_background_image' => 'assets/img/IMG-20241120-WA0018.jpg',
        'why_choose_us' => [] 
    ];
}

// Check if background image exists, otherwise use fallback
$hero_img = !empty($fields['hero_background_image']) ? $fields['hero_background_image'] : 'assets/img/IMG-20241120-WA0018.jpg';
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>SmartStudyPro - Home</title>
  <meta name="description" content="">
  <meta name="keywords" content="">

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
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="">
      </a>

      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php" class="active">Home<br></a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="courses.php">Courses</a></li>
          <li><a href="contact.html">Contact</a></li>
          <li><a href="cart.php" title="Shopping Cart"><i class="bi bi-bag"></i></a></li>
        </ul>
        <i class="mobile-nav-toggle d-xl-none bi bi-list"></i>
      </nav>

      <a class="btn-getstarted" href="courses.php">Get Started</a>

    </div>
  </header>

  <main class="main">

    <section id="hero" class="hero section dark-background">
      <img src="<?php echo $hero_img; ?>" alt="" data-aos="fade-in">

      <div class="container">
        <h2 data-aos="fade-up" data-aos-delay="100"><?php echo $fields['hero_title']; ?></h2>
        <p data-aos="fade-up" data-aos-delay="200"><?php echo $fields['hero_subtitle']; ?></p>
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
              <li><i class="bi bi-check-circle"></i> <span>Manage content and bookings in one place.</span></li>
              <li><i class="bi bi-check-circle"></i> <span>A simple learning experience, powered by a strong foundation.</span></li>
            </ul>
            <a href="about.html" class="read-more"><span>Read More</span><i class="bi bi-arrow-right"></i></a>
          </div>
        </div>
      </div>
    </section>

    <section id="why-us" class="section why-us">
      <div class="container">
        <div class="row gy-4">

          <div class="col-lg-4" data-aos="fade-up" data-aos-delay="100">
            <div class="why-box">
              <h3>Why Choose SmartStudyPro</h3>
              <p>Experience a platform built for students. Manage your learning journey seamlessly from one place.</p>
              <div class="text-center">
                <a href="about.html" class="more-btn"><span>Learn More</span> <i class="bi bi-chevron-right"></i></a>
              </div>
            </div>
          </div>

          <div class="col-lg-8 d-flex align-items-stretch">
            <div class="row gy-4" data-aos="fade-up" data-aos-delay="200">
              
              <?php if(!empty($fields['why_choose_us'])): ?>
                <?php foreach($fields['why_choose_us'] as $feature): ?>
                  <div class="col-xl-4">
                    <div class="icon-box d-flex flex-column justify-content-center align-items-center">
                      <i class="<?php echo $feature['icon_class']; ?>"></i>
                      <h4><?php echo $feature['feature_title']; ?></h4>
                      <p><?php echo $feature['feature_description']; ?></p>
                    </div>
                  </div>
                <?php endforeach; ?>
              <?php else: ?>
                <div class="col-xl-4">
                  <div class="icon-box d-flex flex-column justify-content-center align-items-center">
                    <i class="bi bi-gem"></i>
                    <h4>Quality Education</h4>
                    <p>Standard and verified curriculum.</p>
                  </div>
                </div>
              <?php endif; ?>

            </div>
          </div>

        </div>
      </div>
    </section>

    <section id="features" class="features section">
      <div class="container">
        <div class="row gy-4">
          <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="100">
            <div class="features-item"><i class="bi bi-eye" style="color: #ffbb2c;"></i><h3><a href="">Homework Assistance</a></h3></div>
          </div>
          <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="200">
            <div class="features-item"><i class="bi bi-infinity" style="color: #5578ff;"></i><h3><a href="">Science Projects</a></h3></div>
          </div>
          <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="300">
            <div class="features-item"><i class="bi bi-mortarboard" style="color: #e80368;"></i><h3><a href="">Holiday Packages</a></h3></div>
          </div>
          <div class="col-lg-3 col-md-4" data-aos="fade-up" data-aos-delay="400">
            <div class="features-item"><i class="bi bi-nut" style="color: #e361ff;"></i><h3><a href="">ICT Lessons</a></h3></div>
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
            <p><strong>Phone:</strong> <span>+256 704 416250</span></p>
            <p><strong>Email:</strong> <span>smartstudypro36@gmail.com</span></p>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/purecounter/purecounter_vanilla.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/js/main.js"></script>

</body>
</html>