<?php

require_once __DIR__ . '/../../config.php';

function dpo_api_url(): string {
    return rtrim((string) app_config('DPO_PAY_BASE_URL', 'https://secure.3gdirectpay.com/API/v6/'), '/') . '/';
}

function dpo_checkout_url(string $token): string {
    return 'https://secure.3gdirectpay.com//payv2.php?ID=' . rawurlencode($token);
}

function dpo_request(string $xml): SimpleXMLElement {
    $ch = curl_init(dpo_api_url());
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $xml,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/xml; charset=utf-8',
            'Accept: application/xml',
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_SSL_VERIFYHOST => 2,
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($response === false || $status < 200 || $status >= 300) {
        throw new RuntimeException('DPO request failed: ' . ($error !== '' ? $error : 'HTTP ' . $status));
    }

    libxml_use_internal_errors(true);
    $parsed = simplexml_load_string($response);

    if ($parsed === false) {
        throw new RuntimeException('DPO returned invalid XML.');
    }

    return $parsed;
}

function dpo_create_token(array $transaction, array $services): array {
    $token = app_config('DPO_PAY_COMPANY_TOKEN');

    if ($token === null || trim($token) === '') {
        throw new RuntimeException('DPO Pay Company Token is not configured.');
    }

    $api = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><API3G></API3G>');
    $api->addChild('CompanyToken', $token);
    $api->addChild('Request', 'createToken');

    $tx = $api->addChild('Transaction');
    foreach ([
        'PaymentAmount' => number_format((float) $transaction['amount'], 2, '.', ''),
        'PaymentCurrency' => $transaction['currency'],
        'CompanyRef' => $transaction['company_ref'],
        'CompanyRefUnique' => '1',
        'RedirectURL' => $transaction['redirect_url'],
        'BackURL' => $transaction['back_url'],
        'PTL' => '24',
        'PTLtype' => 'hours',
        'customerFirstName' => $transaction['first_name'],
        'customerLastName' => $transaction['last_name'],
        'customerEmail' => $transaction['email'],
        'customerPhone' => preg_replace('/\D+/', '', $transaction['phone']),
        'customerCountry' => 'UG',
        'TransactionSource' => 'Website',
    ] as $name => $value) {
        if ($value !== '') {
            $tx->addChild($name, htmlspecialchars((string) $value, ENT_XML1 | ENT_COMPAT, 'UTF-8'));
        }
    }

    $servicesNode = $api->addChild('Services');

    foreach ($services as $service) {
        $node = $servicesNode->addChild('Service');
        $node->addChild('ServiceType', $service['type']);
        $node->addChild('ServiceDescription', htmlspecialchars($service['description'], ENT_XML1 | ENT_COMPAT, 'UTF-8'));
        $node->addChild('ServiceDate', $service['date']);
    }

    $response = dpo_request($api->asXML());
    $result = trim((string) ($response->Result ?? $response->Code ?? ''));

    if ($result !== '000') {
        throw new RuntimeException('DPO rejected the transaction: ' . trim((string) ($response->ResultExplanation ?? $response->Explanation ?? 'Unknown DPO error.')));
    }

    $transactionToken = trim((string) ($response->TransToken ?? ''));

    if ($transactionToken === '') {
        throw new RuntimeException('DPO did not return a transaction token.');
    }

    return [
        'result' => $result,
        'result_explanation' => trim((string) ($response->ResultExplanation ?? 'Transaction created.')),
        'trans_token' => $transactionToken,
        'trans_ref' => trim((string) ($response->TransRef ?? '')),
        'raw' => $response->asXML(),
    ];
}

function dpo_verify_token(string $transactionToken, ?string $companyRef = null): array {
    $companyToken = app_config('DPO_PAY_COMPANY_TOKEN');

    if ($companyToken === null || trim($companyToken) === '') {
        throw new RuntimeException('DPO Pay Company Token is not configured.');
    }

    $api = new SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><API3G></API3G>');
    $api->addChild('CompanyToken', $companyToken);
    $api->addChild('Request', 'verifyToken');
    $api->addChild('TransactionToken', $transactionToken);

    if ($companyRef !== null && $companyRef !== '') {
        $api->addChild('CompanyRef', $companyRef);
    }

    $response = dpo_request($api->asXML());
    $result = trim((string) ($response->Result ?? $response->Code ?? ''));

    return [
        'result' => $result,
        'result_explanation' => trim((string) ($response->ResultExplanation ?? $response->Explanation ?? '')),
        'amount' => (float) ($response->TransactionFinalAmount ?? $response->TransactionAmount ?? 0),
        'currency' => strtoupper(trim((string) ($response->TransactionFinalCurrency ?? $response->TransactionCurrency ?? ''))),
        'trans_ref' => trim((string) ($response->TransactionRef ?? $response->TransRef ?? '')),
        'company_ref' => trim((string) ($response->CompanyRef ?? '')),
        'approval' => trim((string) ($response->TransactionApproval ?? $response->ApprovalNumber ?? '')),
        'raw' => $response->asXML(),
    ];
}

function dpo_ensure_payments_table(PDO $db): void {
    $db->exec("CREATE TABLE IF NOT EXISTS dpo_payments (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        account_id INTEGER NOT NULL,
        company_ref TEXT NOT NULL UNIQUE,
        trans_token TEXT UNIQUE,
        trans_ref TEXT,
        amount REAL NOT NULL,
        currency TEXT NOT NULL,
        status TEXT NOT NULL DEFAULT 'pending',
        booking_ids TEXT NOT NULL,
        result_code TEXT,
        result_explanation TEXT,
        raw_response TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
}

function dpo_mark_payment(PDO $db, string $companyRef, array $verification): bool {
    $db->beginTransaction();

    try {
        $stmt = $db->prepare('SELECT * FROM dpo_payments WHERE company_ref = :ref LIMIT 1');
        $stmt->execute([':ref' => $companyRef]);
        $payment = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$payment) {
            $db->rollBack();
            return false;
        }

        $status = match ($verification['result']) {
            '000' => 'paid',
            '001', '002', '003', '005', '007' => 'pending',
            '900' => 'pending',
            '901', '902', '903', '904' => 'failed',
            default => 'pending',
        };

        if ($status === 'paid') {
            $expected = round((float) $payment['amount'], 2);
            $received = round((float) $verification['amount'], 2);

            if ($received !== $expected || strtoupper($verification['currency']) !== strtoupper($payment['currency'])) {
                $status = 'failed';
            }
        }

        $stmt = $db->prepare('UPDATE dpo_payments SET trans_ref = :trans_ref, status = :status, result_code = :result_code, result_explanation = :result_explanation, raw_response = :raw_response, updated_at = CURRENT_TIMESTAMP WHERE id = :id');
        $stmt->execute([
            ':trans_ref' => $verification['trans_ref'],
            ':status' => $status,
            ':result_code' => $verification['result'],
            ':result_explanation' => $verification['result_explanation'],
            ':raw_response' => $verification['raw'],
            ':id' => $payment['id'],
        ]);

        if ($status === 'paid') {
            $ids = json_decode($payment['booking_ids'], true);

            if (!is_array($ids) || !$ids) {
                throw new RuntimeException('Payment contains no booking items.');
            }

            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $stmt = $db->prepare("UPDATE bookings SET paid = 1, account_id = ? WHERE id IN ($placeholders) AND paid = 0");
            $stmt->execute(array_merge([(int) $payment['account_id']], array_map('intval', $ids)));
        }

        $db->commit();
        return $status === 'paid';
    } catch (Throwable $e) {
        if ($db->inTransaction()) {
            $db->rollBack();
        }
        throw $e;
    }
}
