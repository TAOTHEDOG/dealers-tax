<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ?pro_code=10
// ?pro_code=10&amp_code=01
// ?tam_code=100101
$pro_code = isset($_GET['pro_code']) ? trim($_GET['pro_code']) : null;
$amp_code = isset($_GET['amp_code']) ? trim($_GET['amp_code']) : null;
$tam_code = isset($_GET['tam_code']) ? trim($_GET['tam_code']) : null;

try {
    if ($tam_code !== null) {
        // single subdistrict
        $stmt = $pdo->prepare("
            SELECT tam_code, tam_n_t, pro_code, amp_code, longitude, latitude
            FROM subdistricts
            WHERE tam_code = :tam_code
        ");
        $stmt->execute([':tam_code' => $tam_code]);

    } elseif ($pro_code !== null && $amp_code !== null) {
        // filter by province + district
        $pro_code = str_pad($pro_code, 2, '0', STR_PAD_LEFT);
        $amp_code = str_pad($amp_code, 2, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("
            SELECT tam_code, tam_n_t, pro_code, amp_code, longitude, latitude
            FROM subdistricts
            WHERE pro_code = :pro_code AND amp_code = :amp_code
            ORDER BY tam_code ASC
        ");
        $stmt->execute([':pro_code' => $pro_code, ':amp_code' => $amp_code]);

    } elseif ($pro_code !== null) {
        // filter by province only
        $pro_code = str_pad($pro_code, 2, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("
            SELECT tam_code, tam_n_t, pro_code, amp_code, longitude, latitude
            FROM subdistricts
            WHERE pro_code = :pro_code
            ORDER BY amp_code ASC, tam_code ASC
        ");
        $stmt->execute([':pro_code' => $pro_code]);

    } else {
        // all
        $stmt = $pdo->query("
            SELECT tam_code, tam_n_t, pro_code, amp_code, longitude, latitude
            FROM subdistricts
            ORDER BY pro_code ASC, amp_code ASC, tam_code ASC
        ");
    }

    $data = $stmt->fetchAll();

    echo json_encode([
        'success' => true,
        'count'   => count($data),
        'data'    => $data
    ], JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
}
