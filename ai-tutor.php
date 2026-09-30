<?php

require_once __DIR__ . '/content-access.php';
require_login();

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$question = trim((string) ($input['question'] ?? ''));
$bookingId = (int) ($input['booking_id'] ?? 0);

if ($question === '' || mb_strlen($question) > 4000 || $bookingId <= 0) {
    http_response_code(422);
    echo json_encode(['error' => 'Please provide a valid question and resource.']);
    exit;
}

$apiKey = app_config('GEMINI_API_KEY');
if (!$apiKey) {
    http_response_code(503);
    echo json_encode(['error' => 'The SmartStudyPro AI tutor is not configured yet.']);
    exit;
}

$user = current_user();
$booking = paid_booking_for_resource(auth_db(), (int) $user['id'], $bookingId);
if (!$booking || empty($booking['file_path'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Payment is required before the AI tutor can access this resource.']);
    exit;
}

$file = private_resource_path($booking['file_path']);
if (!$file || !resource_extension_allowed($file)) {
    http_response_code(404);
    echo json_encode(['error' => 'The learning resource is unavailable.']);
    exit;
}

$size = filesize($file);
$mime = resource_mime($file);

$prompt = "You are the SmartStudyPro AI tutor. Answer the learner's question using the supplied learning resource as the primary source. Explain clearly at the learner's level, teach rather than merely give the answer, and say when the resource does not contain enough information. Do not invent facts. Resource: " . ($booking['service'] ?? 'Learning Resource') . ". Learner question: " . $question;

$parts = [['text' => $prompt]];

if ($size <= 15 * 1024 * 1024) {
    $parts[] = ['inline_data' => [
        'mime_type' => $mime,
        'data' => base64_encode((string) file_get_contents($file))
    ]];
} else {
    if ($size > 100 * 1024 * 1024) {
        http_response_code(413);
        echo json_encode(['error' => 'This resource is too large for the current AI tutor pipeline.']);
        exit;
    }

    $startHeaders = [
        'X-Goog-Upload-Protocol: resumable',
        'X-Goog-Upload-Command: start',
        'X-Goog-Upload-Header-Content-Length: ' . $size,
        'X-Goog-Upload-Header-Content-Type: ' . $mime,
        'Content-Type: application/json'
    ];
    $start = curl_init('https://generativelanguage.googleapis.com/upload/v1beta/files?key=' . urlencode($apiKey));
    curl_setopt_array($start, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => true,
        CURLOPT_HTTPHEADER => $startHeaders,
        CURLOPT_POSTFIELDS => json_encode(['file' => ['display_name' => basename($file)]])
    ]);
    $startResponse = curl_exec($start);
    $startStatus = curl_getinfo($start, CURLINFO_HTTP_CODE);
    curl_close($start);

    if ($startResponse === false || $startStatus < 200 || $startStatus >= 300 || !preg_match('/x-goog-upload-url:\s*(.+)/i', $startResponse, $match)) {
        http_response_code(502);
        echo json_encode(['error' => 'The AI tutor could not upload the learning resource for analysis.']);
        exit;
    }

    $uploadUrl = trim($match[1]);
    $handle = fopen($file, 'rb');
    $upload = curl_init($uploadUrl);
    curl_setopt_array($upload, [
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Content-Length: ' . $size,
            'X-Goog-Upload-Offset: 0',
            'X-Goog-Upload-Command: upload, finalize'
        ],
        CURLOPT_UPLOAD => true,
        CURLOPT_INFILE => $handle,
        CURLOPT_INFILESIZE => $size,
        CURLOPT_TIMEOUT => 180
    ]);
    $uploadResponse = curl_exec($upload);
    $uploadStatus = curl_getinfo($upload, CURLINFO_HTTP_CODE);
    curl_close($upload);
    fclose($handle);

    if ($uploadResponse === false || $uploadStatus < 200 || $uploadStatus >= 300) {
        http_response_code(502);
        echo json_encode(['error' => 'The AI tutor could not finish processing the learning resource.']);
        exit;
    }

    $uploaded = json_decode($uploadResponse, true);
    $fileName = $uploaded['file']['name'] ?? $uploaded['name'] ?? null;
    if (!$fileName) {
        http_response_code(502);
        echo json_encode(['error' => 'The AI tutor did not receive a valid resource reference.']);
        exit;
    }

    $fileState = 'PROCESSING';
    $fileData = null;
    for ($attempt = 0; $attempt < 30; $attempt++) {
        $check = curl_init('https://generativelanguage.googleapis.com/v1beta/' . $fileName . '?key=' . urlencode($apiKey));
        curl_setopt_array($check, [CURLOPT_RETURNTRANSFER => true, CURLOPT_TIMEOUT => 15]);
        $checkResponse = curl_exec($check);
        curl_close($check);
        $fileData = json_decode((string) $checkResponse, true);
        $fileState = $fileData['state'] ?? $fileData['file']['state'] ?? 'PROCESSING';
        if ($fileState === 'ACTIVE') break;
        if ($fileState === 'FAILED') break;
        sleep(2);
    }

    $uri = $fileData['uri'] ?? $fileData['file']['uri'] ?? null;
    if ($fileState !== 'ACTIVE' || !$uri) {
        http_response_code(502);
        echo json_encode(['error' => 'The AI tutor could not finish processing this resource.']);
        exit;
    }

    $parts[] = ['file_data' => ['mime_type' => $mime, 'file_uri' => $uri]];
}

$payload = [
    'contents' => [[
        'role' => 'user',
        'parts' => $parts
    ]],
    'generationConfig' => [
        'temperature' => 0.35,
        'maxOutputTokens' => 2048
    ]
];

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.8-flash:generateContent?key=' . urlencode($apiKey);
$ch = curl_init($url);
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_SLASHES),
    CURLOPT_TIMEOUT => 90,
]);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($response === false || $status < 200 || $status >= 300) {
    error_log('Gemini tutor error: HTTP ' . $status . ' ' . substr((string) $response, 0, 1000));
    http_response_code(502);
    echo json_encode(['error' => 'The AI tutor could not answer right now. Please try again.']);
    exit;
}

$json = json_decode($response, true);
$text = $json['candidates'][0]['content']['parts'][0]['text'] ?? null;

if (!$text) {
    http_response_code(502);
    echo json_encode(['error' => 'The AI tutor returned an empty answer.']);
    exit;
}

echo json_encode(['answer' => $text], JSON_UNESCAPED_UNICODE);
