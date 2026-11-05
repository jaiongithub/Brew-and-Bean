<?php
// BREW & BEAN Staff Verification Challenge Endpoint
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../config/db.php';

$isStaff = false;
if (isLoggedIn() && in_array($_SESSION['role'], ['staff', 'manager', 'admin'])) {
    $isStaff = true;
} elseif (isset($_COOKIE['staff_secret_token']) && !empty($_COOKIE['staff_secret_token'])) {
    $isStaff = true;
}

if (!$isStaff) {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Forbidden: This challenge endpoint is only accessible within an authenticated staff browser context.'
    ]);
    exit;
}

// Notify Verifier Service if reachable
$verifierUrl = getenv('VERIFIER_INTERNAL_URL') ?: 'http://brew-verifier:8000';
$postData = json_encode([
    'flag_id' => 'BB-F05',
    'source' => 'stored_xss_execution',
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown'
]);

$ch = curl_init("{$verifierUrl}/api/internal/record_flag");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 2);
@curl_exec($ch);
curl_close($ch);

echo json_encode([
    'success' => true,
    'status' => 'XSS_EXPLOIT_VERIFIED',
    'flag_id' => 'BB-F05',
    'flag' => FLAG_F05,
    'message' => 'Stored XSS payload successfully executed inside authenticated Staff Browser context!'
]);

