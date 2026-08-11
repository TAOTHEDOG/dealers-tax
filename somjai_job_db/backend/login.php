<?php
ob_start();
ini_set('display_errors', 0);
error_reporting(E_ALL);

register_shutdown_function(function () {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Fatal: ' . $err['message']]);
    } else {
        ob_end_flush();
    }
});

require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// ── Read input (JSON or form-data) ───────────────────────────
$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (str_contains($contentType, 'application/json')) {
    $body     = json_decode(file_get_contents('php://input'), true);
    $username = trim($body['username'] ?? '');
    $password = trim($body['password'] ?? '');
} else {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
}

// ── Validate input ───────────────────────────────────────────
if ($username === '' || $password === '') {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'กรุณากรอก username และ password']);
    exit;
}

try {
    // ── Find user ────────────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT id, username, password, full_name, role, is_active
        FROM users
        WHERE username = :username
        LIMIT 1
    ");
    $stmt->execute([':username' => $username]);
    $user = $stmt->fetch();

    // ── Verify ───────────────────────────────────────────────
    if (!$user || !password_verify($password, $user['password'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'username หรือ password ไม่ถูกต้อง']);
        exit;
    }

    if (!$user['is_active']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'บัญชีนี้ถูกระงับการใช้งาน']);
        exit;
    }

    // ── Generate token ───────────────────────────────────────
    $token   = bin2hex(random_bytes(32));
    $expires = date('Y-m-d H:i:s', strtotime('+8 hours'));

    // Store token in DB
    $pdo->prepare("
        INSERT INTO user_tokens (user_id, token, expires_at)
        VALUES (:user_id, :token, :expires_at)
    ")->execute([
        ':user_id'    => $user['id'],
        ':token'      => $token,
        ':expires_at' => $expires,
    ]);

    // ── Update last login ────────────────────────────────────
    $pdo->prepare("
        UPDATE users SET updated_at = NOW() WHERE id = :id
    ")->execute([':id' => $user['id']]);

    ob_end_clean();
    echo json_encode([
        'success'  => true,
        'token'    => $token,
        'expires'  => $expires,
        'user'     => [
            'id'        => $user['id'],
            'username'  => $user['username'],
            'full_name' => $user['full_name'],
            'role'      => $user['role'],
        ],
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}