<?php 
include("../config/config.php");
session_start();

// ตรวจสอบว่ามีข้อมูลส่งมาแบบ POST หรือไม่
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $id = isset($_POST['reject_item_id']) ? trim($_POST['reject_item_id']) : '';
    $reject_machineno = isset($_POST['reject_machineno']) ? trim($_POST['reject_machineno']) : '';
    $comment = isset($_POST['comment']) ? trim($_POST['comment']) : '';

    $account_id = isset($_SESSION['id']) ? $_SESSION['id'] : '';
    $username = isset($_SESSION['username']) ? $_SESSION['username'] : '';

    if (!empty($id)) {
        // 1. เริ่ม Transaction เพื่อรับประกันว่าทำงานครบทั้ง 2 คำสั่งในครั้งเดียว
        pg_query($connections, "BEGIN");

        $comment_log = "Reject รายการเลขถัง " . $reject_machineno . " โดยผู้ใช้งานชื่อ " . $username;

        // 2. INSERT Log (ใช้ pg_query_params เพื่อความปลอดภัยและทำงานเร็วขึ้น)
        $query1 = "INSERT INTO acc_histories (account_id, state, item_id, comment) VALUES ($1, 6, $2, $3)";
        $result1 = pg_query_params($connections, $query1, array($account_id, $id, $comment_log));

        // 3. UPDATE Item State
        $update_state = "UPDATE items SET status = 6, comment = $1 WHERE id = $2";
        $result_update = pg_query_params($connections, $update_state, array($comment, $id));

        // 4. ตรวจสอบผลลัพธ์
        if ($result1 && $result_update) {
            pg_query($connections, "COMMIT"); // บันทึกข้อมูลจริงทันที
            $message = urlencode("Reject รายการสำเร็จ");
        } else {
            pg_query($connections, "ROLLBACK"); // หากคำสั่งใดล้มเหลว ให้ยกเลิกทั้งหมด
            $message = urlencode("เกิดข้อผิดพลาดในการบันทึกข้อมูล");
        }
    } else {
        $message = urlencode("ไม่พบรหัสรายการ");
    }

    // ปิดการเชื่อมต่อ DB ก่อนเปลี่ยนหน้า
    if (file_exists("../config/dbcloseconnect.php")) {
        include("../config/dbcloseconnect.php");
    }

    // Redirect กลับไปหน้าเดิมทันที
    header("Location: index.php?message=" . $message);
    exit(0);
}
?>