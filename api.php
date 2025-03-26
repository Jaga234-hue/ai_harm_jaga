<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

$apiKey = 'AIzaSyBlvPtLj0A2udVIuyOx2B7EEfXaG6ltzO0'; // Replace with your actual Google Cloud API key

$response = [
    'image_analysis' => null,
    'video_analysis' => null,
    'text_analysis' => null,
    'errors' => []
];

try {
    // Process uploaded file (image or video)
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $filePath = $_FILES['file']['tmp_name'];
        $mimeType = mime_content_type($filePath);
        $fileContent = base64_encode(file_get_contents($filePath));

        // Image processing
        if (str_starts_with($mimeType, 'image/')) {
            $visionResponse = callVisionApi($fileContent, $apiKey);
            $response['image_analysis'] = parseApiResponse($visionResponse);
        }
        // Video processing
        elseif (str_starts_with($mimeType, 'video/')) {
            $operationName = startVideoProcessing($fileContent, $apiKey);
            $response['video_analysis'] = pollVideoOperation($operationName, $apiKey);
        }
        else {
            $response['errors'][] = 'Unsupported file type';
        }
    }

    // Process text input
    if (!empty($_POST['text'])) {
        $text = $_POST['text'];
        $languageResponse = callLanguageApi($text, $apiKey);
        $textAnalysis = parseApiResponse($languageResponse);
        
        if(isset($textAnalysis['documentSentiment'])) {
            // Add custom sentiment classification
            $score = $textAnalysis['documentSentiment']['score'];
            $textAnalysis['custom_sentiment'] = classifySentiment($score);
        }
        
        $response['text_analysis'] = $textAnalysis;
    }

    echo json_encode($response);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ]);
}

// Custom sentiment classification function
function classifySentiment($score) {
    if ($score >= 0.3) return 'positive';
    if ($score <= -0.3) return 'negative';
    return 'neutral';
}

// Enhanced API response parser
function parseApiResponse($apiResponse) {
    $data = json_decode($apiResponse, true);
    
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid API response format');
    }
    
    if (isset($data['error'])) {
        throw new Exception($data['error']['message'] ?? 'API error');
    }
    
    return $data;
}

function callVisionApi($fileContent, $apiKey) {
    $url = "https://vision.googleapis.com/v1/images:annotate?key={$apiKey}";
    $data = [
        'requests' => [[
            'image' => ['content' => $fileContent],
            'features' => [
                ['type' => 'SAFE_SEARCH_DETECTION'],
                ['type' => 'OBJECT_LOCALIZATION']
            ]
        ]]
    ];
    return sendRequest($url, $data);
}

function startVideoProcessing($fileContent, $apiKey) {
    $url = "https://videointelligence.googleapis.com/v1/videos:annotate?key={$apiKey}";
    $data = [
        'inputContent' => $fileContent,
        'features' => ['EXPLICIT_CONTENT_DETECTION']
    ];
    $result = json_decode(sendRequest($url, $data), true);
    return $result['name'];
}

function pollVideoOperation($operationName, $apiKey) {
    $url = "https://videointelligence.googleapis.com/v1/operations/{$operationName}?key={$apiKey}";
    $startTime = time();
    
    do {
        if (time() - $startTime > 300) { // 5-minute timeout
            throw new Exception('Video processing timeout');
        }
        
        sleep(5);
        $result = json_decode(sendRequest($url, null, 'GET'), true);
    } while (!isset($result['done']) || !$result['done']);

    return $result;
}

function callLanguageApi($text, $apiKey) {
    $url = "https://language.googleapis.com/v1/documents:analyzeSentiment?key={$apiKey}";
    $data = [
        'document' => [
            'type' => 'PLAIN_TEXT',
            'content' => $text
        ],
        'encodingType' => 'UTF8'
    ];
    return sendRequest($url, $data);
}

function sendRequest($url, $data = null, $method = 'POST') {
    $ch = curl_init($url);
    
    $options = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 30
    ];

    if ($method === 'POST' && $data) {
        $options[CURLOPT_POST] = true;
        $options[CURLOPT_POSTFIELDS] = json_encode($data);
    }

    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    
    if (curl_errno($ch)) {
        throw new Exception('API request failed: ' . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpCode >= 400) {
        $errorResponse = json_decode($response, true);
        $errorMessage = $errorResponse['error']['message'] ?? "HTTP error {$httpCode}";
        throw new Exception("API Error: {$errorMessage}");
    }

    curl_close($ch);
    return $response;
}
?>