<?php

require_once __DIR__ . '/auth.php';

require_login();

$status = $_GET['status'] ?? 'pending';
$companyRef = trim((string) ($_GET['ref'] ?? ''));
$db = auth_db();
dpo_ensure_payments_table($db);

$stmt = $db->prepare('SELECT * FROM dpo_payments WHERE company_ref = :ref AND account_id = :account_id LIMIT 1');
$stmt->execute([
    ':ref' => $companyRef,
    ':account_id' => current_user()['id'],
]);
$payment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$payment) {
    http_response_code(404);
    exit('Payment not found.');
}

if ($payment['status'] === 'paid') {
    $status = 'paid';
} elseif ($payment['status'] === 'failed') {
    $status = 'failed';
} else {
    $status = 'pending';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Payment Status - SmartStudyPro</title>
<link href="assets/vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
<style>
body{font-family:Arial,sans-serif;background:#f8fafc;color:#1e293b;min-height:100vh;display:grid;place-items:center}
.card{max-width:620px;width:calc(100% - 32px);padding:40px;border-radius:20px;background:#fff;border:1px solid #e2e8f0;box-shadow:0 15px 45px rgba(15,23,42,.08);text-align:center}
h1{color:#0c086b}.amount{font-size:2rem;font-weight:800;color:#ff7a00}
</style>
</head>
<body>
<div class="card">
<?php if ($status === 'paid'): ?>
    <div class="amount">Payment successful</div>
    <h1>Thank you</h1>
    <p>Your SmartStudyPro purchase has been confirmed.</p>
    <a class="btn btn-primary" href="profile.php">View My Account</a>
<?php elseif ($status === 'failed'): ?>
    <h1>Payment not completed</h1>
    <p><?= htmlspecialchars($payment['result_explanation'] ?: 'The payment was not completed.') ?></p>
    <a class="btn btn-primary" href="checkout.php">Try Again</a>
<?php else: ?>
    <h1>Payment is being confirmed</h1>
    <p>Your payment has not yet reached a final status. You can return to checkout or refresh this page after a moment.</p>
    <a class="btn btn-primary" href="checkout.php">Back to Checkout</a>
<?php endif; ?>
</div>
</body>
</html>
