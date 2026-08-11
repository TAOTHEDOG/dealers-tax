<?php
ob_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json; charset=utf-8');

register_shutdown_function(function() {
    $err = error_get_last();
    if ($err) {
        $out = ob_get_clean();
        http_response_code(500);
        echo json_encode([
            'fatal'   => $err['message'],
            'file'    => $err['file'],
            'line'    => $err['line'],
            'output'  => $out,
        ], JSON_UNESCAPED_UNICODE);
    } else {
        echo ob_get_clean();
    }
});

try {
    require_once 'config.php';

    // Test minimal INSERT into applications
    $sql = "
        INSERT INTO applications (
            position1, expected_salary,
            full_name, nickname, phone, birth_date,
            marital_status,
            cur_house_no, cur_province_id, cur_district_id,
            reg_house_no, reg_province_id, reg_district_id,
            emergency_name, emergency_relation, emergency_phone
        ) VALUES (
            'TEST', 0,
            'TEST', 'TEST', '0000000000', '2000-01-01',
            'โสด',
            '1', '10', 1001,
            '1', '10', 1001,
            'TEST', 'TEST', '0000000000'
        ) RETURNING id
    ";

    $stmt  = $pdo->prepare($sql);
    $stmt->execute();
    $row   = $stmt->fetch();
    $appId = $row['id'];

    // Delete test record
    $pdo->exec("DELETE FROM applications WHERE id = $appId");

    ob_end_clean();
    echo json_encode([
        'success' => true,
        'message' => "INSERT/DELETE test passed — app id was: $appId",
    ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
    ob_end_clean();
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error'   => $e->getMessage(),
        'line'    => $e->getLine(),
    ], JSON_UNESCAPED_UNICODE);
}