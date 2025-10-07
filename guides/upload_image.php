<?php
header("Content-Type: application/json");

require __DIR__ . "/../accounts/auth.php";
require __DIR__ . "/../vendor/autoload.php";

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

$userId = checklogin();
if (!$userId) {
    http_response_code(403);
    echo json_encode(['error' => 'Not logged in']);
    exit();
}

// Max upload size in MB
$MAX_SIZE_MB = 10;
$MAX_SIZE_BYTES = $MAX_SIZE_MB * 1024 * 1024;

if (empty($_FILES['file']['tmp_name'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded']);
    exit();
}

// Check for PHP upload errors
if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Upload failed with error code ' . $_FILES['file']['error']]);
    exit();
}

// Check file size
if ($_FILES['file']['size'] > $MAX_SIZE_BYTES) {
    http_response_code(400);
    echo json_encode(['error' => "File too large. Maximum size is {$MAX_SIZE_MB} MB"]);
    exit();
}

// Validate file extension
$ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array(strtolower($ext), $allowed)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type']);
    exit();
}

// Read file and encode for ImgBB
$fileData = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
$apiKey = $_ENV['IMGBB_API_KEY'];

$postData = http_build_query([
    'key' => $apiKey,
    'image' => $fileData,
    'name' => uniqid('img_', true)
]);

$ch = curl_init('https://api.imgbb.com/1/upload');
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
if ($response === false) {
    http_response_code(500);
    echo json_encode(['error' => curl_error($ch)]);
    exit();
}
curl_close($ch);

$result = json_decode($response, true);

// Check ImgBB response
if (!isset($result['success']) || !$result['success']) {
    http_response_code(500);
    $msg = $result['error']['message'] ?? 'ImgBB upload failed';
    echo json_encode(['error' => $msg]);
    exit();
}

// Success
echo json_encode(['location' => $result['data']['url']]);

?>