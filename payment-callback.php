<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/app/Services/DpoPayService.php';

if (payment_mode() !== 'production') {
    http_response_code(204);
    exit;
}

$token = trim((string) ($_GET['TransactionToken'] ?? $_GET['TransToken'] ?? $_POST['TransactionToken'] ?? $_POST['TransToken'] ?? ''));
$companyRef = trim((string) ($_GET['CompanyRef'] ?? $_POST['CompanyRef'] ?? ''));

try {
    $db = auth_db();
    dpo_ensure_payments_table($db);

    if ($token === '' && $companyRef !== '') {
        $stmt = $db->prepare('SELECT trans_token FROM dpo_payments WHERE company_ref = :ref LIMIT 1');
        $stmt->execute([':ref' => $companyRef]);
        $token = trim((string) $stmt->fetchColumn());
    }

    if ($token === '') {
        http_response_code(400);
        exit('Missing transaction token.');
    }

    $verification = dpo_verify_token($token, $companyRef !== '' ? $companyRef : null);

    if ($companyRef === '') {
        $companyRef = $verification['company_ref'];
    }

    if ($companyRef !== '') {
        dpo_mark_payment($db, $companyRef, $verification);
    }

    http_response_code(200);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'OK';
} catch (Throwable $e) {
    error_log('DPO Pay callback error: ' . $e->getMessage());
    http_response_code(500);
    echo 'ERROR';
}
