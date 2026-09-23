<?php
/**
 * backend/ajax_index.php — DataTables server-side processing endpoint (Admin/Accountant view)
 * Called via AJAX by backend/index.php
 */

session_start();

include("./../config/config.php");
include("./../config/ssDB.php");

if (!isset($_SESSION) || (($_SESSION['role'] != "Accountant") && ($_SESSION['role'] != "Admin") && ($_SESSION['role'] != "CS"))) {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$draw = intval($_POST['draw'] ?? 1);
$start = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 25);
$search = pg_escape_string($connections, $_POST['search']['value'] ?? '');
$status_filter_param = $_POST['status'] ?? 'all';

// Columns available for ordering
$columns = [
    0 => 'users.dealer',
    1 => 'items.branchno',
    2 => 'items.created_at',
    3 => 'items.lastmachine_no',
    4 => 'items.customername',
    5 => 'items.status',  // manage col — not sortable
    6 => 'items.status',
    7 => 'items.ap_date',
];

$orderIdx = intval($_POST['order'][0]['column'] ?? 2);
$orderDir = ($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$orderCol = $columns[$orderIdx] ?? 'items.created_at';

// Base filter: not checked, no test records
$base_filter = "items.ischeck = FALSE
                AND (items.tax_no <> 'TEST      ' OR items.tax_no IS NULL)";

// เพิ่มเงื่อนไขการกรองตามสถานะ (Status Filter)
if ($status_filter_param === '1') {
    // รอดำเนินการ (status 1)
    $base_filter .= " AND items.status = '1'";
} elseif ($status_filter_param === '2') {
    // Processing (status 4)
    $base_filter .= " AND (items.status = '2' OR items.status = '3')";
} elseif ($status_filter_param === '3') {
    // จ่ายแล้ว (status 5)
    $base_filter .= " AND items.status = '5'";
} elseif ($status_filter_param === '6') {
    // Reject (status 6)
    $base_filter .= " AND items.status = '6'";
} elseif ($status_filter_param === '7') {
    // ยกเลิก (status 7)
    $base_filter .= " AND items.status = '7'";
} else {
    // กรณีเลือกดูทั้งหมด (all) หรือไม่ได้ส่งค่ามา จะซ่อน status 7 เป็นค่าเริ่มต้นตามเดิม
    $base_filter .= " AND items.status <> '7'";
}

$search_filter = '';
if ($search !== '') {
    $search_filter = " AND (items.lastmachine_no ILIKE '%$search%'
                        OR items.customername ILIKE '%$search%'
                        OR items.branchno ILIKE '%$search%'
                        OR users.dealer ILIKE '%$search%'
                        OR items.created_at::varchar ILIKE '%$search%'
                        OR items.ap_date::varchar ILIKE '%$search%')";
}

$join = "FROM items LEFT JOIN users ON items.dealer_id = users.id";

// Total (unfiltered)
$r_total = pg_query($connections, "SELECT COUNT(*) $join WHERE $base_filter");
$total = (int) pg_fetch_result($r_total, 0, 0);

// Filtered count
$r_filt = pg_query($connections, "SELECT COUNT(*) $join WHERE $base_filter $search_filter");
$filtered = (int) pg_fetch_result($r_filt, 0, 0);

// Data
$query = "SELECT items.*, users.dealer, users.name, users.branchgroup
           $join
           WHERE $base_filter $search_filter
           ORDER BY $orderCol $orderDir
           LIMIT $length OFFSET $start";
$result = pg_query($connections, $query);

$is_cs = ($_SESSION['role'] == "CS");
$is_admin = ($_SESSION['role'] == "Admin");
$hostname = $hostname ?? '';
$year_thai = (int) date('Y') + 543;
$update_thaidate = $year_thai . date('-m-d');

$data = [];
while ($row = pg_fetch_object($result)) {

    // Date display
    $created_at = $row->created_at;

    // 1. รับค่าเข้า DateTime
    $date = new DateTime($created_at);

    // 2. ปรับ Timezone ให้เป็นไทย (Asia/Bangkok)
    $date->setTimezone(new DateTimeZone('Asia/Bangkok'));

    // 3. แยกแสดงผล 2 คอลัมน์
    $year_buddhist = (int) $date->format('Y') + 543;

    // คอลัมน์: วันที่บันทึก (เช่น 2569-09-22)
    $date_disp = $year_buddhist . '-' . $date->format('m-d');

    // คอลัมน์: เวลาบันทึก (เช่น 17:19)
    $time_disp = $date->format('H:i');

    // Status label
    $status_map = [
        '1' => 'Pending',
        '2' => 'Processing',
        '3' => 'Processing',
        '4' => 'Edit machineno',
        '5' => 'Paid',
        '6' => 'Reject',
        '7' => 'Cancel'
    ];
    $status_label = $status_map[$row->status] ?? '—';

    $badge_class = [
        'Pending' => 'badge-pending',
        'Processing' => 'badge-processing',
        'Edit machineno' => 'badge-pending',
        'Paid' => 'badge-paid',
        'Reject' => 'badge-reject',
        'Cancel' => 'badge-reject',
    ][$status_label] ?? '';
    $status_html = "<span class='badge-status $badge_class'>$status_label</span>";

    // Action buttons
    $actions = "<a href='#' class='btn-check-action btn btn-sm me-1'
                    onclick='show_data_file({$row->id},{$row->status})'>Check</a>";

    if (!$is_cs) {
        if ($row->status == '1' || $row->status == '2') {
            $actions .= "<a href='#' class='btn-reject-action btn btn-sm me-1'
                            onclick='confirm_comment({$row->id})'>Reject</a>";
        } elseif ($row->status == '3') {
            if ($is_admin) {
                $actions .= "<a href='#' class='btn-edit-action btn btn-sm me-1'
                                onclick='edit_machineno({$row->id})'>แก้เลขถัง</a>";
            }
        }
    }

    // Payment status logic
    $ap_no_empty = ($row->ap_no == null || $row->ap_no == '');
    if ($row->status == '5' && ($ap_no_empty || $update_thaidate <= $row->ap_date)) {
        $status_label = 'PaymentPending';
        $badge_class = 'badge-payment-pending';
        $status_html = "<span class='badge-status $badge_class'>$status_label</span>";
    }

    // Display AP number, date, and amount
    $ap_no_disp = htmlspecialchars($row->ap_no ?? '');
    $amount_disp = ($row->ap_total !== null) ? number_format((float) $row->ap_total, 2) . ' ฿' : '';
    $ap_date_disp = htmlspecialchars($row->ap_date ?? '');
    if ($ap_date_disp <= '2569-08-01') {
        $ap_no_disp = '';
        $ap_date_disp = '';
        $amount_disp = '';
    }

    $data[] = [
        htmlspecialchars($row->dealer ?? ''),
        htmlspecialchars($row->branchno),
        $date_disp,
        $time_disp,
        htmlspecialchars($row->lastmachine_no),
        htmlspecialchars($row->customername),
        $actions,
        $status_html,
        $ap_date_disp,
        $ap_no_disp,
        $amount_disp,
    ];
}

include("./../config/ssDBclose.php");

header('Content-Type: application/json');
echo json_encode([
    'draw' => $draw,
    'recordsTotal' => $total,
    'recordsFiltered' => $filtered,
    'data' => $data,
]);