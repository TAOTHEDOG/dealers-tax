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
require_once 'auth.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

$id = (int) ($_GET['id'] ?? 0);
if (!$id) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing id']);
    exit;
}

try {
    // ── Main application ─────────────────────────────────────
    $stmt = $pdo->prepare("
        SELECT a.*,
            a.cur_province_id    AS cur_province_name,
            a.cur_district_id    AS cur_district_name,
            a.cur_subdistrict_id AS cur_subdistrict_name,
            a.reg_province_id    AS reg_province_name,
            a.reg_district_id    AS reg_district_name,
            a.reg_subdistrict_id AS reg_subdistrict_name
        FROM applications a
        WHERE a.id = :id
    ");
    $stmt->execute([':id' => $id]);
    $app = $stmt->fetch();

    if (!$app) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'ไม่พบใบสมัคร']);
        exit;
    }

    // ── Work histories ───────────────────────────────────────
    $wStmt = $pdo->prepare("
        SELECT * FROM work_histories
        WHERE application_id = :id
        ORDER BY seq ASC
    ");
    $wStmt->execute([':id' => $id]);
    $works = $wStmt->fetchAll();

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'data' => $app,
        'work_histories' => $works,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}