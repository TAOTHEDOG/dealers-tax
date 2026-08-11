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
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// ── Query params ─────────────────────────────────────────────
$page = max(1, (int) ($_GET['page'] ?? 1));
$limit = min(100, max(1, (int) ($_GET['limit'] ?? 20)));
$offset = ($page - 1) * $limit;

$search = trim($_GET['search'] ?? '');   // search by name / phone
$status = trim($_GET['status'] ?? '');   // รับเรื่อง / สัมภาษณ์ / ผ่าน / ไม่ผ่าน / สำรอง
$position = trim($_GET['position'] ?? '');   // filter by position1
$dateFrom = trim($_GET['date_from'] ?? '');  // YYYY-MM-DD
$dateTo = trim($_GET['date_to'] ?? '');  // YYYY-MM-DD
$sortBy = trim($_GET['sort_by'] ?? 'submitted_at');
$sortDir = strtoupper(trim($_GET['sort_dir'] ?? 'DESC')) === 'ASC' ? 'ASC' : 'DESC';

// Whitelist sort columns
$allowedSort = ['submitted_at', 'full_name', 'position1', 'expected_salary', 'status'];
if (!in_array($sortBy, $allowedSort))
    $sortBy = 'submitted_at';

try {
    // ── Build WHERE ──────────────────────────────────────────
    $where = ['1=1'];
    $params = [];

    if ($search !== '') {
        $where[] = "(a.full_name ILIKE :search OR a.phone ILIKE :search OR a.nickname ILIKE :search OR a.position1 ILIKE :search OR a.position2 ILIKE :search)";
        $params[':search'] = '%' . $search . '%';
    }
    if ($status !== '') {
        $where[] = "a.status = :status";
        $params[':status'] = $status;
    }
    if ($position !== '') {
        $where[] = "(a.position1 ILIKE :position OR a.position2 ILIKE :position)";
        $params[':position'] = '%' . $position . '%';
    }
    if ($dateFrom !== '') {
        $where[] = "a.submitted_at::date >= :date_from::date";
        $params[':date_from'] = $dateFrom;
    }
    if ($dateTo !== '') {
        $where[] = "a.submitted_at::date <= :date_to::date";
        $params[':date_to'] = $dateTo;
    }

    $whereSQL = implode(' AND ', $where);

    // ── Count total ──────────────────────────────────────────
    $countStmt = $pdo->prepare("
        SELECT COUNT(*) AS total
        FROM applications a
        WHERE $whereSQL
    ");
    $countStmt->execute($params);
    $total = (int) $countStmt->fetch()['total'];

    // ── Fetch rows ───────────────────────────────────────────
    $dataStmt = $pdo->prepare("
        SELECT
            a.id,
            a.submitted_at,
            a.status,
            a.position1,
            a.position2,
            a.expected_salary,
            a.full_name,
            a.nickname,
            a.phone,
            a.birth_date,
            a.age,
            a.education_level,
            a.marital_status,
            a.can_drive_car,
            a.can_drive_moto,
            a.profile_image_path,
            a.cur_province_id AS cur_province_name,
            a.cur_district_id AS cur_district_name
        FROM applications a
        WHERE $whereSQL
        ORDER BY a.$sortBy $sortDir
        LIMIT :limit OFFSET :offset
    ");

    $params[':limit'] = $limit;
    $params[':offset'] = $offset;
    $dataStmt->execute($params);
    $rows = $dataStmt->fetchAll();

    // ── Summary counts by status ─────────────────────────────
    $summaryStmt = $pdo->prepare("
        SELECT status, COUNT(*) AS count
        FROM applications
        GROUP BY status
        ORDER BY status
    ");
    $summaryStmt->execute();
    $summary = [];
    foreach ($summaryStmt->fetchAll() as $row) {
        $summary[$row['status']] = (int) $row['count'];
    }

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'total' => $total,
        'page' => $page,
        'limit' => $limit,
        'total_pages' => (int) ceil($total / $limit),
        'summary' => $summary,
        'data' => $rows,
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}