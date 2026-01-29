<?php 
require_once 'cms-init.php'; 

$contact = null;

try {
    if (function_exists('cockpit')) {
        // Confirmed Model Name: ContactDetails
        $contact = cockpit('content')->item('ContactDetails'); 
    }
} catch (Exception $e) {
    $contact = null; 
}

/** * Mapping keys from diagnostic.
 * Using strip_tags() to remove hidden <p> tags from the Address field
 */
$phone   = !empty($contact['phone'])   ? strip_tags($contact['phone'])   : '+256 704 416250';
$email   = !empty($contact['email'])   ? strip_tags($contact['email'])   : 'smartstudypro36@gmail.com';
$address = !empty($contact['address']) ? strip_tags($contact['address']) : 'Kampala, Uganda';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Contact - SmartStudyPro</title>
  
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

<body class="contact-page">

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
          <li><a href="contact.php" class="active">Contact</a></li>
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
              <h1>Contact</h1>
              <p class="mb-0">Reach out to us for any inquiries or support. We are here to help you on your educational journey.</p>
            </div>
          </div>
        </div>
      </div>
      <nav class="breadcrumbs">
        <div class="container">
          <ol>
            <li><a href="index.php">Home</a></li>
            <li class="current">Contact</li>
          </ol>
        </div>
      </nav>
    </div>

    <section id="contact" class="contact section">
      <div class="mb-5" data-aos="fade-up" data-aos-delay="200">
        <iframe style="border:0; width: 100%; height: 300px;" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15959.0270477025!2d32.5694863!3d0.313611!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x177dbc0917056f71%3A0x2863e46c98ee3e40!2sKampala!5e0!3m2!1sen!2sug!4v1700000000000!5m2!1sen!2sug" allowfullscreen="" loading="lazy"></iframe>
      </div>

      <div class="container" data-aos="fade-up" data-aos-delay="100">
        <div class="row gy-4">

          <div class="col-lg-4">
            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="300">
              <i class="bi bi-geo-alt flex-shrink-0"></i>
              <div>
                <h3>Address</h3>
                <p><?= htmlspecialchars($address) ?></p>
              </div>
            </div>

            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="400">
              <i class="bi bi-telephone flex-shrink-0"></i>
              <div>
                <h3>Call Us</h3>
                <p><?= htmlspecialchars($phone) ?></p>
              </div>
            </div>

            <div class="info-item d-flex" data-aos="fade-up" data-aos-delay="500">
              <i class="bi bi-envelope flex-shrink-0"></i>
              <div>
                <h3>Email Us</h3>
                <p><?= htmlspecialchars($email) ?></p>
              </div>
            </div>
          </div>

          <div class="col-lg-8">
            <form action="contact-process.php" method="post" class="php-email-form" data-aos="fade-up" data-aos-delay="200">
              <div class="row gy-4">
                <div class="col-md-6">
                  <input type="text" name="name" class="form-control" placeholder="Your Name" required="">
                </div>
                <div class="col-md-6">
                  <input type="email" class="form-control" name="email" placeholder="Your Email" required="">
                </div>
                <div class="col-md-12">
                  <input type="text" class="form-control" name="subject" placeholder="Subject" required="">
                </div>
                <div class="col-md-12">
                  <textarea class="form-control" name="message" rows="6" placeholder="Message" required=""></textarea>
                </div>
                <div class="col-md-12 text-center">
                  <div class="loading">Loading</div>
                  <div class="error-message"></div>
                  <div class="sent-message">Your message has been sent. Thank you!</div>
                  <button type="submit">Send Message</button>
                </div>
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