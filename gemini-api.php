<?php
/**
 * SmartStudyPro V2.5 - PRODUCTION Chat API
 */
header('Content-Type: application/json');

$input = json_decode(file_get_contents('php://input'), true);
$question = $input['question'] ?? '';
$context = $input['context'] ?? '';
$api_key = "AIzaSyCf8eYw2ovMXTCRPdu09d-cKDMyZzXJP4U"; // Replace with your key

if (!$question) {
    echo json_encode(['answer' => 'No question provided.']);
    exit;
}

// Production uses the high-speed 2.0 Flash model
$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key=" . $api_key;

$payload = [
    "contents" => [[
        "parts" => [["text" => "Context: $context \n\n User Question: $question"]]
    ]],
    "generationConfig" => [
        "temperature" => 0.7,
        "maxOutputTokens" => 800
    ]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

$answer = $result['candidates'][0]['content']['parts'][0]['text'] ?? "I'm sorry, I couldn't process that request right now.";

echo json_encode(['answer' => $answer]);