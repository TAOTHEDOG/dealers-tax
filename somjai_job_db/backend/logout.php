<?php
ob_start();
ini_set('display_errors', 0);

require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$token = getBearerToken();

if (!$token) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'No token provided']);
    exit;
}

try {
    $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE token = :token");
    $stmt->execute([':token' => $token]);

    ob_end_clean();
    echo json_encode(['success' => true, 'message' => 'ออกจากระบบเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}

function getBearerToken(): ?string {
    $headers = getallheaders();
    $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (str_starts_with($auth, 'Bearer ')) {
        return trim(substr($auth, 7));
    }
    return null;
}