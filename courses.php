<?php
require_once 'config.php';

try {
    // Fetches all individual pages created under the 'course' Page Type
    $response = $butterCms->fetchPages('course');
    $coursePages = $response->getPages();
} catch (Exception $e) {
    // Prevents the site from crashing if the API is down
    $coursePages = [];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Courses - SmartStudyPro</title>

  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="apple-touch-icon">

  <link href="https://fonts.googleapis.com" rel="preconnect">
  <link href="https://fonts.gstatic.com" rel="preconnect" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,300;1,400;1,500;1,600;1,700;1,800&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800&display=swap" rel="stylesheet">

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/vendor/aos/aos.css" rel="stylesheet">
  <link href="assets/vendor/glightbox/css/glightbox.min.css" rel="stylesheet">
  <link href="assets/vendor/swiper/swiper-bundle.min.css" rel="stylesheet">

  <link href="assets/css/main.css" rel="stylesheet">
</head>

<body class="courses-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl position-relative d-flex align-items-center">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="SmartStudyPro Logo">
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="about.php">About</a></li>
          <li><a href="courses.php" class="active">Courses</a></li>
          <li><a href="contact.html">Contact</a></li>
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
              <h1>Courses</h1>
              <p class="mb-0">Explore our professional learning programs designed for your success.</p>
            </div>
          </div>
        </div>
      </div>
      <nav class="breadcrumbs">
        <div class="container">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li class="current">Courses</li>
          </ol>
        </div>
      </nav>
    </div>

    <section id="courses" class="courses section">
      <div class="container">
        <div class="row">

          <?php if (!empty($coursePages)): ?>
            <?php foreach ($coursePages as $page): 
                $fields = $page->getFields(); 
                $slug = $page->getSlug();
                
                // Content Logic
                $title = $fields['title'] ?? ($fields['service_name'] ?? $page->getName());
                $image = !empty($fields['featured_image']) ? $fields['featured_image'] : 'assets/img/course-1.jpg';
                $price = $fields['price'] ?? '0';
                $category = $fields['category'] ?? 'General';
            ?>
              <div class="col-lg-4 col-md-6 d-flex align-items-stretch mt-4" data-aos="zoom-in" data-aos-delay="100">
                <div class="course-item">
                  <img src="<?php echo $image; ?>" class="img-fluid" alt="<?php echo htmlspecialchars($title); ?>" style="width:100%; height:250px; object-fit:cover;">
                  <div class="course-content">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                      <p class="category"><?php echo htmlspecialchars($category); ?></p>
                      <p class="price">$<?php echo htmlspecialchars($price); ?></p>
                    </div>

                    <h3><a href="course-details.php?slug=<?php echo $slug; ?>">
                      <?php echo htmlspecialchars($title); ?>
                    </a></h3>
                    
                    <p class="description">
                        <?php 
                        $desc = strip_tags($fields['description'] ?? '');
                        echo (strlen($desc) > 110) ? substr($desc, 0, 110) . '...' : $desc; 
                        ?>
                    </p>
                  </div>
                </div>
              </div> 
            <?php endforeach; ?>
          <?php else: ?>
            <div class="col-12 text-center py-5">
              <h3>No courses available.</h3>
              <p>Please check back later or add content in ButterCMS.</p>
            </div>
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
            <p><strong>Phone:</strong> +256 704 416250</p>
            <p><strong>Email:</strong> smartstudypro36@gmail.com</p>
          </div>
        </div>
      </div>
    </div>
  </footer>

  <a href="#" id="scroll-top" class="scroll-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

  <script src="assets/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
  <script src="assets/vendor/aos/aos.js"></script>
  <script src="assets/vendor/glightbox/js/glightbox.min.js"></script>
  <script src="assets/vendor/swiper/swiper-bundle.min.js"></script>
  <script src="assets/js/main.js"></script>

</body>
</html>