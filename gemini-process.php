<?php
/**
 * SmartStudyPro V2.5 - PRODUCTION Video Processor
 */

function processVideoWithGemini($videoPath, $lessonTitle) {
    $apiKey = "AIzaSyDjdPAkzUyWtMQFmJCobqIR-Hwv8RWDsI0";
    $model = "gemini-2.0-flash";

    if (!file_exists($videoPath)) return "Error: Video file not found locally.";

    // --- STEP 1: RESUMABLE UPLOAD INITIALIZATION ---
    $uploadUrl = "https://generativelanguage.googleapis.com/upload/v1beta/files?key=" . $apiKey;
    $headers = [
        "X-Goog-Upload-Protocol: resumable",
        "X-Goog-Upload-Command: start",
        "X-Goog-Upload-Header-Content-Length: " . filesize($videoPath),
        "X-Goog-Upload-Header-Content-Type: video/mp4",
        "Content-Type: application/json"
    ];

    $ch = curl_init($uploadUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["file" => ["display_name" => $lessonTitle]]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $resp = curl_exec($ch);
    
    preg_match('/location: (.*)/i', $resp, $matches);
    if (!isset($matches[1])) return "Error: Failed to initiate upload.";
    $uploadLocation = trim($matches[1]);
    curl_close($ch);

    // --- STEP 2: PHYSICAL UPLOAD ---
    $videoData = file_get_contents($videoPath);
    $ch = curl_init($uploadLocation);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    curl_setopt($ch, CURLOPT_POSTFIELDS, $videoData);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $uploadResult = json_decode(curl_exec($ch), true);
    $fileUri = $uploadResult['file']['uri'] ?? null;
    curl_close($ch);

    if (!$fileUri) return "Error: Video upload failed.";

    // --- STEP 3: ANALYZE & TRANSCRIBE ---
    // Wait briefly for Google to process the video index
    sleep(5); 

    $aiUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key=" . $apiKey;
    $payload = [
        "contents" => [["parts" => [
            ["text" => "Act as a professional transcriber. Provide a clear transcript of this video followed by a bulleted summary of key learning points."],
            ["file_data" => ["mime_type" => "video/mp4", "file_uri" => $fileUri]]
        ]]]
    ];

    $ch = curl_init($aiUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = json_decode(curl_exec($ch), true);
    curl_close($ch);

    return $result['candidates'][0]['content']['parts'][0]['text'] ?? "AI could not generate notes for this video.";
}

