<?php
require_once 'config.php';

try {
    // Fetches the page with the slug 'about' or 'about-us'
    // Ensure 'about' matches your Page Type ID in ButterCMS
    $response = $butterCms->fetchPage('about', 'about-us');
    $fields = $response->getFields();
    
    $title = $fields['title'] ?? 'About Us';
    $description = $fields['description'] ?? 'Learn more about our mission.';
    $content = $fields['content'] ?? '<p>Default about content goes here.</p>';
    $image = !empty($fields['featured_image']) ? $fields['featured_image'] : 'assets/img/about.jpg';
} catch (Exception $e) {
    // Fallback if the API fails or page isn't found
    $title = "About Us";
    $content = "SmartStudyPro is dedicated to providing expert academic assistance.";
    $image = "assets/img/about.jpg";
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>About - SmartStudyPro</title>

  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@300;400;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

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
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="Logo">
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php" class="active">About</a></li>
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

    <div class="page-title" data-aos="fade">
      <div class="heading">
        <div class="container text-center">
          <h1><?php echo htmlspecialchars($title); ?></h1>
          <p class="mb-0">Empowering students through innovative learning and expert guidance.</p>
        </div>
      </div>
      <nav class="breadcrumbs">
        <div class="container">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li class="current">About</li>
          </ol>
        </div>
      </nav>
    </div>

    <section id="about-us" class="about-us section">
      <div class="container">
        <div class="row gy-4">

          <div class="col-lg-6 order-1 order-lg-2" data-aos="fade-up" data-aos-delay="100">
            <img src="<?php echo $image; ?>" class="img-fluid rounded shadow" alt="About Image">
          </div>

          <div class="col-lg-6 order-2 order-lg-1 content" data-aos="fade-up" data-aos-delay="200">
            <h3><?php echo htmlspecialchars($title); ?></h3>
            <p class="fst-italic">
              <?php echo $description; ?>
            </p>
            <div class="dynamic-content">
                <?php echo $content; ?>
            </div>
          </div>

        </div>
      </div>
    </section>



  </main>

  <footer id="footer" class="footer position-relative light-background text-center py-4 border-top">
    <p>© <strong>SmartStudyPro</strong> - All Rights Reserved</p>
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