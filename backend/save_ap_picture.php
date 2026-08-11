<?php
/*
 * Run this SQL once in your PostgreSQL database to create the required table:
 *
 * CREATE TABLE ap_pictures (
 *     id            BIGSERIAL    PRIMARY KEY,
 *     ap_no         VARCHAR(15)  NOT NULL,
 *     filename      VARCHAR(100) NOT NULL,
 *     original_name VARCHAR(100) NOT NULL,
 *     created_at    TIMESTAMP    DEFAULT CURRENT_TIMESTAMP,
 *     created_by    BIGINT       NOT NULL
 * );
 */

include("./../config/config.php");
session_start();

if (!isset($_SESSION) || (($_SESSION['role'] != "Accountant") && ($_SESSION['role'] != "Admin") && ($_SESSION['role'] != "CS"))) {
    echo "<script>location.href='" . $hostname . "/login.php'</script>";
    return false;
}

$ap_no = trim($_POST['ap_no'] ?? '');
$created_by = (int)$_SESSION['id'];

if ($ap_no === '') {
    header("Location: upload_ap_picture.php?message=" . urlencode("กรุณาใส่เลขที่ AP") . "&status=error");
    exit;
}

$ap_no_check = pg_escape_string($ap_no);
$check = pg_query($connections, "SELECT id FROM items WHERE ap_no = '$ap_no_check' LIMIT 1");
if (!$check || pg_num_rows($check) === 0) {
    header("Location: upload_ap_picture.php?message=" . urlencode("ไม่พบเลขที่ AP \"$ap_no\" ในระบบ") . "&status=error");
    exit;
}

if (empty($_FILES['picture']['name']) || (is_array($_FILES['picture']['name']) && empty($_FILES['picture']['name'][0]))) {
    header("Location: upload_ap_picture.php?message=" . urlencode("กรุณาเลือกไฟล์") . "&status=error");
    exit;
}

// Normalize single-file submission (name="picture") into array form (name="picture[]")
if (!is_array($_FILES['picture']['name'])) {
    foreach ($_FILES['picture'] as $key => $value) {
        $_FILES['picture'][$key] = [$value];
    }
}

$allowed_exts = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'pdf'];
$upload_dir   = __DIR__ . '/../docs/';
$ap_no_safe   = pg_escape_string($ap_no);

$uploaded    = [];
$failed      = [];
$file_count  = count($_FILES['picture']['name']);

for ($i = 0; $i < $file_count; $i++) {
    if ($_FILES['picture']['error'][$i] !== UPLOAD_ERR_OK || $_FILES['picture']['name'][$i] === '') {
        $failed[] = $_FILES['picture']['name'][$i] ?: "ไฟล์ที่ " . ($i + 1);
        continue;
    }

    if ($_FILES['picture']['size'][$i] > 20 * 1024 * 1024) {
        $failed[] = basename($_FILES['picture']['name'][$i]) . " (เกิน 20 MB)";
        continue;
    }

    $ext = strtolower(pathinfo($_FILES['picture']['name'][$i], PATHINFO_EXTENSION));
    if (!in_array($ext, $allowed_exts)) {
        $failed[] = basename($_FILES['picture']['name'][$i]) . " (ประเภทไฟล์ไม่รองรับ)";
        continue;
    }

    $original_name = basename($_FILES['picture']['name'][$i]);
    $ext           = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
    $filename      = 'ap_' . mt_rand(1000000, 9999999) . date("YmdHis") . $i . '.' . $ext;

    if (!move_uploaded_file($_FILES['picture']['tmp_name'][$i], $upload_dir . $filename)) {
        $failed[] = $original_name . " (อัปโหลดไม่สำเร็จ)";
        continue;
    }

    $filename_safe   = pg_escape_string($filename);
    $orig_name_safe  = pg_escape_string($original_name);

    $sql = "INSERT INTO ap_pictures (ap_no, filename, original_name, created_by, created_at)
         VALUES ('$ap_no_safe', '$filename_safe', '$orig_name_safe', $created_by, NOW() AT TIME ZONE 'Asia/Bangkok')";

    $result = pg_query($connections,$sql);

    if ($result) {
        $uploaded[] = $original_name;
    } else {
        @unlink($upload_dir . $filename);
        $failed[] = $original_name . " (บันทึกข้อมูลไม่สำเร็จ) " . $sql;
    }
}

include("../config/dbcloseconnect.php");

if (!empty($uploaded) && empty($failed)) {
    $msg = "อัปโหลดสำเร็จ " . count($uploaded) . " ไฟล์";
    header("Location: upload_ap_picture.php?message=" . urlencode($msg));
} elseif (!empty($uploaded)) {
    $msg = "อัปโหลดสำเร็จ " . count($uploaded) . " ไฟล์ / ไม่สำเร็จ " . count($failed) . " ไฟล์";
    header("Location: upload_ap_picture.php?message=" . urlencode($msg));
} else {
    $reason = !empty($failed) ? $failed[0] : "ไม่มีไฟล์";
    header("Location: upload_ap_picture.php?message=" . urlencode($reason) . "&status=error");
}
exit;
