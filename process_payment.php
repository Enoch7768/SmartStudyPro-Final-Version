<?php
require_once 'auth.php';
require_login(); // Must be signed in to pay — redirects to login.php otherwise

date_default_timezone_set('Africa/Kampala');

if($_SERVER['REQUEST_METHOD'] != 'POST') die("Invalid access");

$user_id = $_POST['user_id'] ?? '';
$payment_method = $_POST['payment_method'] ?? 'Not Specified';
$submitted_total = (float) preg_replace('/[^0-9.]/', '', (string) ($_POST['total'] ?? 0));
$accountId = current_user()['id'];

$db_file = __DIR__ . "/database/bookings.db";
if(!file_exists($db_file)) die("Database not found.");

$bookings = []; 

try {
    $db = new PDO("sqlite:$db_file");
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    ensure_bookings_columns($db);

    $transactionTime = date('Y-m-d H:i:s');

    // Compute the authoritative total from the DB rather than trusting the
    // client-submitted 'total' field, and use it instead of the posted value
    // before marking anything as paid. This does not replace a real payment
    // gateway integration (no money actually changes hands here), but it
    // prevents a tampered/low total posted to this endpoint from being
    // accepted at face value for display or record-keeping.
    // Matches by cart cookie OR logged-in account, same as checkout.php.
    $stmt = $db->prepare("SELECT COALESCE(SUM(CAST(REPLACE(REPLACE(price,'UGX',''),',','') AS REAL)),0) AS total FROM bookings WHERE paid=0 AND (user_id=:user_id OR account_id=:account_id)");
    $stmt->execute([':user_id' => $user_id, ':account_id' => $accountId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $server_total = round((float) ($row['total'] ?? 0), 2);

    if ($server_total <= 0) {
        die("No unpaid bookings found for this user.");
    }
    // Use the amount actually owed on the server, regardless of what the client posted
    $total = $server_total;

    $stmt = $db->prepare("UPDATE bookings SET paid=1, created_at=:now, account_id=:account_id WHERE paid=0 AND (user_id=:user_id OR account_id=:account_id2)");
    $stmt->execute([':user_id' => $user_id, ':now' => $transactionTime, ':account_id' => $accountId, ':account_id2' => $accountId]);

    $recentLimit = date('Y-m-d H:i:s', strtotime('-60 seconds'));
    $stmt = $db->prepare("SELECT * FROM bookings WHERE paid=1 AND created_at >= :recent AND (user_id=:user_id OR account_id=:account_id) ORDER BY created_at DESC");
    $stmt->execute([':user_id' => $user_id, ':account_id' => $accountId, ':recent' => $recentLimit]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if(empty($bookings)) {
        $stmt = $db->prepare("SELECT * FROM bookings WHERE paid=1 AND (user_id=:user_id OR account_id=:account_id) ORDER BY created_at DESC LIMIT 5");
        $stmt->execute([':user_id' => $user_id, ':account_id' => $accountId]);
        $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $success = true;
} catch(Exception $e){
    error_log("process_payment.php error: " . $e->getMessage());
    $error = "We couldn't process your payment. Please try again or contact support.";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Receipt - SmartStudyPro</title>
  <link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link href="assets/vendor/bootstrap-icons/bootstrap-icons.css" rel="stylesheet">
  <link href="assets/css/main.css" rel="stylesheet">
  <link rel="shortcut icon" href="Smart_Study_Logo_Fin-removebg-preview.png" type="image/x-icon">
  <style>
    .receipt-wrapper { max-width: 800px; margin: 40px auto; }
    .receipt-card { background: #fff; border-radius: 20px; box-shadow: 0 15px 50px rgba(0,0,0,0.1); border: none; overflow: hidden; }
    .receipt-header { background: #5fcf80; color: white; padding: 40px; }
    .btn-action { background: #5fcf80; border: none; color: white; transition: 0.3s; }
    .btn-action:hover { background: #3eb462; transform: translateY(-2px); color: white; }
    @media print { .no-print { display: none; } .receipt-card { box-shadow: none; border: 1px solid #eee; } }
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

  <main class="container receipt-wrapper px-3">
    <?php if(isset($success) && $success): ?>
      <div class="card receipt-card">
        <div class="receipt-header text-center">
          <i class="bi bi-patch-check-fill" style="font-size: 4rem;"></i>
          <h2 class="fw-bold mt-2">Payment Successful</h2>
          <p class="mb-0">Thank you for your order via <?= htmlspecialchars($payment_method) ?></p>
        </div>
        
        <div class="card-body p-4 p-md-5">
          <div class="row mb-4 border-bottom pb-3">
            <div class="col-6">
              <span class="text-muted small text-uppercase fw-bold">Amount Paid</span>
              <h3 class="fw-bold text-success">UGX <?= number_format($total) ?></h3>
            </div>
            <div class="col-6 text-end">
              <span class="text-muted small text-uppercase fw-bold">Date & Time</span>
              <p class="fw-semibold mb-0"><?= date('M d, Y') ?></p>
              <small class="text-muted"><?= date('h:i A') ?> (EAT)</small>
            </div>
          </div>

          <h5 class="fw-bold mb-4">Your Purchased Resources</h5>
          <div class="table-responsive">
            <table class="table align-middle">
              <thead>
                <tr class="text-muted small">
                  <th>DESCRIPTION</th>
                  <th class="text-end">ACCESS</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach($bookings as $item): ?>
                  <tr>
                    <td>
                      <div class="fw-bold text-dark"><?= htmlspecialchars($item['service']) ?></div>
                      <small class="text-muted">ID: #<?= str_pad($item['id'], 6, "0", STR_PAD_LEFT) ?></small>
                    </td>
                    <td class="text-end">
                      <?php 
                      $isCourse = (stripos($item['service'], 'Course') !== false); 
                      
                      if($isCourse): ?>
                        <a href="study.php?course_id=<?= $item['id'] ?>" class="btn btn-action btn-sm rounded-pill px-4 shadow-sm">
                          <i class="bi bi-play-circle-fill me-1"></i> Start Learning
                        </a>
                      <?php elseif(!empty($item['file_path'])): ?>
                        <a href="download.php?file=<?= urlencode($item['file_path']) ?>" class="btn btn-dark btn-sm rounded-pill px-4 shadow-sm">
                          <i class="bi bi-cloud-arrow-down-fill me-1"></i> Download
                        </a>
                      <?php else: ?>
                        <span class="badge bg-light text-secondary border px-3">Physical/Service</span>
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
            <a href="index.php" class="btn btn-success rounded-pill px-4" style="background:#5fcf80; border:none;">Back to Home</a>
          </div>
        </div>
      </div>

    <?php elseif(isset($error)): ?>
      <div class="alert alert-danger p-5 rounded-4 text-center shadow-sm">
        <i class="bi bi-exclamation-octagon" style="font-size: 3rem;"></i>
        <h3 class="mt-3">Error Processing Order</h3>
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