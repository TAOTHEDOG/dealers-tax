<?php
include("./config/config.php");
session_start();

header('Content-Type: application/json; charset=utf-8');

$lastmachine_no = isset($_POST['lastmachine_no']) ? trim($_POST['lastmachine_no']) : '';
$id = isset($_SESSION['id']) ? trim((string)$_SESSION['id']) : '';

if ($lastmachine_no === '' || $id === '') {
    echo json_encode(['exists' => false]);
    exit;
}

$lastmachine_no = pg_escape_string($connections, $lastmachine_no);
$id = pg_escape_string($connections, $id);

$query = "SELECT 1 FROM items WHERE lastmachine_no = '$lastmachine_no' AND status NOT IN ('6', '7') AND dealer_id = '$id' LIMIT 1";
$result = pg_query($connections, $query);

if (!$result) {
    echo json_encode(['exists' => false]);
    exit;
}

echo json_encode(['exists' => pg_num_rows($result) > 0]);
