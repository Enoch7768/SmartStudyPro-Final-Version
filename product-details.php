<?php 
require_once 'cms-init.php'; 

$product = null;
$productId = $_GET['id'] ?? null;

if (function_exists('cockpit') && $productId) {
    $product = cockpit('content')->item('Products', ['_id' => $productId]);
}

if (!$product) {
    header("Location: products.php");
    exit;
}

$displayTitle = $product['Title'] ?? 'Product Details';
$displayPrice = $product['Price'] ?? '0';
$displayDesc  = $product['Description'] ?? 'No description available.';
$productType  = $product['ProductType'] ?? 'Online'; 
$category     = $product['Category'] ?? 'General';

$imgData = $product['Image'] ?? null;
$imgUrl = !empty($imgData['path']) 
          ? '/schoolprojectt/SmartStudyProV2.3/cms/storage/uploads' . $imgData['path'] 
          : 'assets/img/course-details-tab-1.png';

$filePath = $product['ProductFile']['path'] ?? '';


$seoTitle       = $product['SEO-Title'] ?? ($displayTitle . " | SmartStudyPro Store");
$seoDescription = $product['SEO-Description'] ?? substr(strip_tags($displayDesc), 0, 160);
$siteUrl        = "https://smartstudypro.com";
$fullImgUrl     = $siteUrl . $imgUrl;
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  
  <title><?= htmlspecialchars($seoTitle) ?></title>
  <meta name="description" content="<?= htmlspecialchars($seoDescription) ?>">

  <meta property="og:type" content="product">
  <meta property="og:url" content="<?= $siteUrl ?>/product-details.php?id=<?= $productId ?>">
  <meta property="og:title" content="<?= htmlspecialchars($seoTitle) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($seoDescription) ?>">
  <meta property="og:image" content="<?= $fullImgUrl ?>">

  <meta property="twitter:card" content="summary_large_image">
  <meta property="twitter:title" content="<?= htmlspecialchars($seoTitle) ?>">
  <meta property="twitter:description" content="<?= htmlspecialchars($seoDescription) ?>">

  <script type="application/ld+json">
  {
    "@context": "https://schema.org/",
    "@type": "Product",
    "name": "<?= htmlspecialchars($displayTitle) ?>",
    "image": "<?= $fullImgUrl ?>",
    "description": "<?= htmlspecialchars(strip_tags($displayDesc)) ?>",
    "brand": {
      "@type": "Brand",
      "name": "SmartStudyPro"
    },
    "offers": {
      "@type": "Offer",
      "url": "<?= $siteUrl ?>/product-details.php?id=<?= $productId ?>",
      "priceCurrency": "UGX",
      "price": "<?= $displayPrice ?>",
      "availability": "https://schema.org/InStock"
    }
  }
  </script>

  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="icon">
  <link href="Smart_Study_Logo_Fin-removebg-preview.png" rel="apple-touch-icon">
</head>

<body class="course-details-page">

  <header id="header" class="header d-flex align-items-center sticky-top">
    <div class="container-fluid container-xl d-flex align-items-center me-auto">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="Logo">
      </a>
      <nav id="navmenu" class="navmenu">
        <ul>
          <li><a href="index.php">Home</a></li>
          <li><a href="courses.php">Courses</a></li>
          <li><a href="products.php" class="active">Products</a></li>
          <li><a href="cart.php"><i class="bi bi-bag"></i></a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main class="main">
    <div class="page-title">
      <div class="heading">
        <div class="container text-center">
          <h1><?= htmlspecialchars($displayTitle) ?></h1>
          <p class="mb-0"><?= htmlspecialchars($category) ?></p>
        </div>
      </div>
    </div>

    <section class="section mt-5">
      <div class="container">
        <div class="row">
          
          <div class="col-lg-8">
            <img src="<?= $imgUrl ?>" class="img-fluid rounded shadow-sm mb-4" alt="">
            <h3>About this Resource</h3>
            <div class="content">
                <?= $displayDesc ?>
            </div>
          </div>

          <div class="col-lg-4">
            <div class="p-4 border rounded shadow-sm bg-light">
              <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="m-0">Price</h4>
                <span class="h3 text-success m-0">UGX <?= number_format(floatval($displayPrice ?? 0), 2) ?></span>
              </div>
              <p class="text-muted small"><i class="bi bi-info-circle"></i> Type: <?= htmlspecialchars($productType) ?></p>
              <hr>
              
              <form action="booking.php" method="post">
                <input type="hidden" name="product_id" value="<?= htmlspecialchars($productId) ?>">
                <!-- item_name/item_price/product_file are looked up server-side in
                     booking.php from product_id; kept here only as a display fallback -->
                <input type="hidden" name="item_name" value="<?= htmlspecialchars($displayTitle) ?>">
                <input type="hidden" name="item_price" value="<?= htmlspecialchars($displayPrice) ?>">
                <input type="hidden" name="product_file" value="<?= htmlspecialchars($filePath) ?>">
                
                <div class="mb-3">
                  <label class="form-label small">Full Name</label>
                  <input type="text" name="name" class="form-control" required>
                </div>
                <div class="mb-3">
                  <label class="form-label small">Email Address</label>
                  <input type="email" name="email" class="form-control" required>
                </div>
                
                <?php if($productType === 'Physical'): ?>
                  <div class="mb-3">
                    <label class="form-label small">Shipping Address</label>
                    <textarea name="address" class="form-control" rows="2" required></textarea>
                  </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-success w-100 py-2 rounded-pill" style="background:#5fcf80; border:none;">
                    Add to Cart
                </button>
              </form>
            </div>
          </div>

        </div>
      </div>
    </section>
  </main>

  <footer id="footer" class="footer mt-5 border-top pt-4">
     <div class="container text-center">
        <p>© 2026 SmartStudyPro. Empowering Education in Uganda.</p>
     </div>
  </footer>

</body>
</html>