<?php

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/app/Services/DpoPayService.php';

if (payment_mode() !== 'production') {
    header('Location: checkout.php');
    exit;
}

$token = trim((string) ($_GET['TransactionToken'] ?? $_GET['TransToken'] ?? $_POST['TransactionToken'] ?? $_POST['TransToken'] ?? ''));
$companyRef = trim((string) ($_GET['CompanyRef'] ?? $_POST['CompanyRef'] ?? ''));

if ($token === '' && $companyRef === '') {
    http_response_code(400);
    exit('Missing DPO transaction reference.');
}

try {
    $db = auth_db();
    dpo_ensure_payments_table($db);

    if ($token === '' && $companyRef !== '') {
        $stmt = $db->prepare('SELECT trans_token FROM dpo_payments WHERE company_ref = :ref LIMIT 1');
        $stmt->execute([':ref' => $companyRef]);
        $token = trim((string) $stmt->fetchColumn());
    }

    if ($token === '') {
        throw new RuntimeException('Unknown DPO transaction.');
    }

    $verification = dpo_verify_token($token, $companyRef !== '' ? $companyRef : null);
    $resolvedRef = $companyRef !== '' ? $companyRef : $verification['company_ref'];

    if ($resolvedRef === '') {
        $stmt = $db->prepare('SELECT company_ref FROM dpo_payments WHERE trans_token = :token LIMIT 1');
        $stmt->execute([':token' => $token]);
        $resolvedRef = trim((string) $stmt->fetchColumn());
    }

    if ($resolvedRef === '' || !dpo_mark_payment($db, $resolvedRef, $verification)) {
        header('Location: payment-result.php?status=pending&ref=' . rawurlencode($resolvedRef));
        exit;
    }

    header('Location: payment-result.php?status=paid&ref=' . rawurlencode($resolvedRef));
    exit;
} catch (Throwable $e) {
    error_log('DPO Pay return error: ' . $e->getMessage());
    header('Location: payment-result.php?status=error');
    exit;
}
