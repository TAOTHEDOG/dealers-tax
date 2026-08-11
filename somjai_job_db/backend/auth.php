<?php
// ── Include this file in any protected API ───────────────────
// require_once 'auth.php';
// $currentUser is available after include

function getBearerToken(): ?string {
    $headers = getallheaders();
    $auth    = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    if (str_starts_with($auth, 'Bearer ')) {
        return trim(substr($auth, 7));
    }
    // Also check query param for testing
    return $_GET['token'] ?? null;
}

function requireAuth(PDO $pdo): array {
    $token = getBearerToken();

    if (!$token) {
        http_response_code(401);
        echo json_encode(['success' => false, 'error' => 'Unauthorized: No token']);
        exit;
    }

    try {
        $stmt = $pdo->prepare("
            SELECT u.id, u.username, u.full_name, u.role, u.is_active,
                   t.expires_at
            FROM user_tokens t
            JOIN users u ON u.id = t.user_id
            WHERE t.token = :token
            LIMIT 1
        ");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Unauthorized: Invalid token']);
            exit;
        }

        if (!$user['is_active']) {
            http_response_code(403);
            echo json_encode(['success' => false, 'error' => 'บัญชีนี้ถูกระงับการใช้งาน']);
            exit;
        }

        if (strtotime($user['expires_at']) < time()) {
            // Delete expired token
            $pdo->prepare("DELETE FROM user_tokens WHERE token = :token")
                ->execute([':token' => $token]);
            http_response_code(401);
            echo json_encode(['success' => false, 'error' => 'Token หมดอายุ กรุณา login ใหม่']);
            exit;
        }

        return $user;

    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        exit;
    }
}

function requireRole(array $user, array $roles): void {
    if (!in_array($user['role'], $roles)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'ไม่มีสิทธิ์เข้าถึง']);
        exit;
    }
}

// Auto-authenticate
$currentUser = requireAuth($pdo);