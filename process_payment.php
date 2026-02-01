<?php
/**
 * FINAL PROCESS PAYMENT - SmartStudyPro
 * Features: "Clean" receipt filtering & SQLite schema auto-fixes.
 */

if($_SERVER['REQUEST_METHOD'] != 'POST') die("Invalid access");

$user_id = $_POST['user_id'] ?? '';
$payment_method = $_POST['payment_method'] ?? 'Not Specified';
$total = $_POST['total'] ?? 0;

$db_file = __DIR__ . "/database/bookings.db";
if(!file_exists($db_file)) die("Database not found.");

$bookings = []; 

try {
    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- SCHEMA AUTO-FIX ---
    $tableInfo = $db->query("PRAGMA table_info(bookings)")->fetchAll(PDO::FETCH_ASSOC);
    $existingColumns = array_column($tableInfo, 'name');

    if (!in_array('created_at', $existingColumns)) {
        $db->exec("ALTER TABLE bookings ADD COLUMN created_at DATETIME");
    }
    if (!in_array('file_path', $existingColumns)) {
        $db->exec("ALTER TABLE bookings ADD COLUMN file_path TEXT");
    }

    // --- TRANSACTION LOGIC ---
    // 1. Capture the exact timestamp of this payment
    $transactionTime = date('Y-m-d H:i:s');

    // 2. Mark items as paid AND update their timestamp to "now"
    $stmt = $db->prepare("UPDATE bookings SET paid=1, created_at=:now WHERE user_id=:user_id AND paid=0");
    $stmt->execute([':user_id' => $user_id, ':now' => $transactionTime]);

    // 3. Fetch ONLY items from THIS transaction (Clean Receipt)
    // We look for items paid in the last minute to isolate this specific order
    $recentLimit = date('Y-m-d H:i:s', strtotime('-60 seconds'));
    $stmt = $db->prepare("SELECT * FROM bookings WHERE user_id=:user_id AND paid=1 AND created_at >= :recent ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $user_id, ':recent' => $recentLimit]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 4. If nothing is found (e.g. page refreshed), show all paid items as a fallback
    if(empty($bookings)) {
        $stmt = $db->prepare("SELECT * FROM bookings WHERE user_id=:user_id AND paid=1 ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([':user_id' => $user_id]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $success = true;
} catch(Exception $e){
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Payment Successful - SmartStudyPro</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <link rel="shortcut icon" href="Smart_Study_Logo_Fin-removebg-preview.png" type="image/x-icon">
  <style>
    .receipt-wrapper { max-width: 800px; margin: 40px auto; }
    .receipt-card { background: #fff; border-radius: 15px; box-shadow: 0 10px 40px rgba(0,0,0,0.1); border: none; }
    .receipt-header { background: #5fcf80; color: white; border-radius: 15px 15px 0 0; padding: 30px; }
    .btn-download { background: #5fcf80; border: none; color: white; }
    .btn-download:hover { background: #3eb462; color: white; }
    @media print { .no-print { display: none; } }
  </style>
</head>
<body class="bg-light">

  <header id="header" class="header d-flex align-items-center sticky-top no-print">
    <div class="container-fluid container-xl d-flex align-items-center me-auto">
      <a href="index.php" class="logo d-flex align-items-center me-auto">
        <img src="Smart_Study_Logo_Fin-removebg-preview.png" alt="Logo">
      </a>
    </div>
  </header>

  <main class="container receipt-wrapper">
    <?php if(isset($success) && $success): ?>
      <div class="card receipt-card">
        <div class="receipt-header text-center">
          <i class="bi bi-check-circle-fill" style="font-size: 3.5rem;"></i>
          <h2 class="fw-bold mt-2">Payment Confirmed</h2>
          <p class="mb-0">Order completed successfully via <?= htmlspecialchars($payment_method) ?></p>
        </div>
        
        <div class="card-body p-4 p-md-5">
          <div class="d-flex justify-content-between mb-4 border-bottom pb-3">
            <div>
              <span class="text-muted small">TOTAL PAID</span>
              <h4 class="fw-bold text-success">UGX <?= number_format($total, 2) ?></h4>
            </div>
            <div class="text-end">
              <span class="text-muted small">ORDER DATE</span>
              <p class="fw-semibold mb-0"><?= date('M d, Y') ?></p>
            </div>
          </div>

          <h5 class="fw-bold mb-3">Your Digital Resources</h5>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr class="text-muted small">
                  <th>ITEM DESCRIPTION</th>
                  <th class="text-end">ACTION</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($bookings as $item): ?>
                  <tr>
                    <td>
                      <div class="fw-semibold"><?= htmlspecialchars($item['service']) ?></div>
                      <small class="text-muted">Transaction ID: #<?= $item['id'] ?></small>
                    </td>
                    <td class="text-end">
                      <?php if(!empty($item['file_path'])): ?>
                        <a href="download.php?file=<?= urlencode($item['file_path']) ?>" class="btn btn-download btn-sm rounded-pill px-4 shadow-sm">
                          <i class="bi bi-cloud-arrow-down"></i> Download
                        </a>
                      <?php else: ?>
                        <span class="badge bg-light text-secondary border">Service/Physical</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="text-center mt-5 no-print">
            <button onclick="window.print()" class="btn btn-outline-dark rounded-pill px-4 me-2">
              <i class="bi bi-printer"></i> Print Receipt
            </button>
            <a href="products.php" class="btn btn-success rounded-pill px-4" style="background:#5fcf80; border:none;">Return to Shop</a>
          </div>
        </div>
      </div>

    <?php elseif(isset($error)): ?>
      <div class="alert alert-danger p-5 rounded-4 text-center">
        <i class="bi bi-exclamation-triangle" style="font-size: 3rem;"></i>
        <h3 class="mt-3">Processing Error</h3>
        <p><?= htmlspecialchars($error) ?></p>
        <a href="checkout.php" class="btn btn-dark rounded-pill mt-3 px-5">Try Again</a>
      </div>
    <?php endif; ?>
  </main>

  <footer class="text-center py-4 text-muted small no-print">
    <p>© 2026 SmartStudyPro - Matugga, Uganda</p>
  </footer>

</body>
</html>