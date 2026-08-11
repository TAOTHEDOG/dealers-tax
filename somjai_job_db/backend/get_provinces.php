<?php
require_once 'config.php';
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

try {
    $stmt = $pdo->query("
        SELECT pro_code, pro_n_t, pro_n_e, longitude, latitude
        FROM provinces
        ORDER BY pro_code ASC
    ");
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
