<?php
header('Content-Type: application/json; charset=utf-8');

$result = [];

// 1. PHP version
$result['php_version'] = PHP_VERSION;

// 2. PDO PostgreSQL available?
$result['pdo_pgsql'] = extension_loaded('pdo_pgsql') ? 'yes' : 'NO - not installed';

// 3. Try config.php
try {
    require_once 'config.php';
    $result['config'] = 'OK';
} catch (Throwable $e) {
    $result['config_error'] = $e->getMessage();
    echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
    exit;
}

// 4. Try DB connection
try {
    $stmt = $pdo->query('SELECT NOW() as now');
    $row  = $stmt->fetch();
    $result['db_connection'] = 'OK';
    $result['db_time']       = $row['now'];
} catch (Throwable $e) {
    $result['db_error'] = $e->getMessage();
}

// 5. Check tables exist
try {
    $stmt = $pdo->query("
        SELECT table_name
        FROM information_schema.tables
        WHERE table_schema = 'public'
        ORDER BY table_name
    ");
    $result['tables'] = $stmt->fetchAll(PDO::FETCH_COLUMN);
} catch (Throwable $e) {
    $result['tables_error'] = $e->getMessage();
}

echo json_encode($result, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);