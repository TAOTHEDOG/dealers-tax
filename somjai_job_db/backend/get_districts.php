<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

// ?pro_code=10
$pro_code = isset($_GET['pro_code']) ? trim($_GET['pro_code']) : null;

try {
    if ($pro_code !== null) {
        $pro_code = str_pad($pro_code, 2, '0', STR_PAD_LEFT);
        $stmt = $pdo->prepare("
            SELECT dit_id, dit_n_t, pro_code, dit_code, longitude, latitude
            FROM districts
            WHERE pro_code = :pro_code
            ORDER BY dit_code ASC
        ");
        $stmt->execute([':pro_code' => $pro_code]);
    } else {
        $stmt = $pdo->query("
            SELECT dit_id, dit_n_t, pro_code, dit_code, longitude, latitude
            FROM districts
            ORDER BY pro_code ASC, dit_code ASC
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
