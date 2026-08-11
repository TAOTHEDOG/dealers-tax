<?php
session_start();
if ((!isset($_SESSION) || count($_SESSION) == 0) && $_SESSION['role'] != "dealer") {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}
include("./config/config.php");
include("./config/ssDB.php");

$branchgroup = $_SESSION['branchgroup'];
$sql_branch = "select branchcode as cmpcode from branch where etax_group = '$branchgroup'";
$q_branch = pg_query($connection, $sql_branch);
$branchList = [];
while ($r = pg_fetch_object($q_branch)) {
    $branchList[] = "'" . pg_escape_string($connections, $r->cmpcode) . "'";
}
if (empty($branchList)) {
    echo json_encode(['draw' => intval($_POST['draw'] ?? 1), 'recordsTotal' => 0, 'recordsFiltered' => 0, 'data' => []]);
    exit;
}
$branchno = implode(',', $branchList);

$draw = intval($_POST['draw'] ?? 1);
$start = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 25);
$search = pg_escape_string($connections, $_POST['search']['value'] ?? '');

// col index: 0=created_at,1=branchno,2=lastmachine_no,3=customername,4=doc1(orderable:false),5=status,6=ap_no,7=ap_date,8=ap_total,9=comment,10=id(orderable:false)
$columns = ['created_at', 'branchno', 'lastmachine_no', 'customername', 'doc1', 'status', 'ap_no', 'ap_date', 'ap_total', 'comment', 'id'];
$orderIdx = intval($_POST['order'][0]['column'] ?? 0);
$orderDir = ($_POST['order'][0]['dir'] ?? 'desc') === 'asc' ? 'ASC' : 'DESC';
$orderCol = $columns[$orderIdx] ?? 'created_at';

$where = "branchno IN ($branchno)";
if ($search !== '') {
    $where .= " AND (
        created_at::varchar  ILIKE '%$search%' OR
        branchno             ILIKE '%$search%' OR
        lastmachine_no       ILIKE '%$search%' OR
        customername         ILIKE '%$search%' OR
        ap_no                ILIKE '%$search%' OR
        ap_date::varchar     ILIKE '%$search%' OR
        ap_total::varchar    ILIKE '%$search%' OR
        comment              ILIKE '%$search%'
    )";
}

$r_total = pg_query($connections, "SELECT COUNT(*) FROM items WHERE branchno IN ($branchno)");
$total = (int) pg_fetch_result($r_total, 0, 0);
$r_filt = pg_query($connections, "SELECT COUNT(*) FROM items WHERE $where");
$filtered = (int) pg_fetch_result($r_filt, 0, 0);

$result = pg_query($connections, "SELECT * FROM items WHERE $where ORDER BY $orderCol $orderDir LIMIT $length OFFSET $start");

$current_date = date("Y-m-d");
$year_thai = (int) date('Y') + 543;
$update_thaidate = $year_thai . date('-m-d');
$time_h = (int) date('H');

$data = [];
while ($row = pg_fetch_object($result)) {
    $ap_no_empty = ($row->ap_no == null || $row->ap_no == '');
    switch ((int) $row->status) {
        case 1:
            $status = 'Pending';
            break;
        case 2:
        case 3:
        case 4:
            $status = 'Processing';
            break;
        case 5:
            if ($ap_no_empty || $update_thaidate < $row->ap_date) {
                $status = 'Processing';
            } elseif ($update_thaidate == $row->ap_date) {
                $status = $time_h < 17 ? 'Processing' : 'Paid';
            } else {
                $status = 'Paid';
            }
            break;
        case 6:
            $status = 'Reject';
            break;
        case 7:
            $status = 'Cancel';
            break;
        default:
            $status = '—';
    }
    $year_disp = ((int) substr($row->created_at, 0, 4)) + 543;
    $date_disp = $year_disp . substr($row->created_at, 4, 7);

    $ap_date_disp = '';
    if (!$ap_no_empty) {
        if ($update_thaidate > $row->ap_date || ($update_thaidate == $row->ap_date && $time_h >= 17))
            $ap_date_disp = $row->ap_date;
    }

    $badge = ['Pending' => 'badge-pending', 'Processing' => 'badge-processing', 'Paid' => 'badge-paid', 'Reject' => 'badge-reject', 'Cancel' => 'badge-cancel'][$status] ?? '';
    $status_html = "<span class='badge-status $badge'>$status</span>";

    $doc_links = '';
    $doc_labels = ['doc1' => 'ใบกำกับภาษีค่ารถ', 'doc2' => 'Commission', 'doc3' => 'เอกสารรับเงินดาวน์', 'doc4' => 'ซับดาวน์', 'doc5' => 'ซับงวด'];
    foreach ($doc_labels as $f => $l) {
        if (!empty($row->$f))
            $doc_links .= "<a href='./docs/{$row->$f}' download class='d-block py-1' style='color:var(--purple-600);text-decoration:none;'>⬇ $l</a>";
    }

    $ap_pic_section = '';
    if (!$ap_no_empty) {
        $ap_no_q = pg_escape_string($connections, $row->ap_no);
        $pic_result = pg_query($connections, "SELECT filename, original_name FROM ap_pictures WHERE ap_no = '$ap_no_q' ORDER BY created_at");
        if ($pic_result && pg_num_rows($pic_result) > 0) {
            $ap_pic_section = "<hr><h6 class='fw-bold mb-2' style='color:var(--purple-700);'>📸 รูปภาพ AP</h6>";
            while ($pic = pg_fetch_object($pic_result)) {
                $ap_pic_section .= "<a href='./docs/" . htmlspecialchars($pic->filename) . "' target='_blank' class='d-block py-1' style='color:var(--purple-600);text-decoration:none;'>⬇ " . htmlspecialchars($pic->original_name) . "</a>";
            }
        }
    }

    $mid = 'fileModal' . $row->id;
    $btn_file = "<button class='btn-file btn' data-bs-toggle='modal' data-bs-target='#$mid'>📎 เอกสาร</button>
    <div class='modal fade' id='$mid' tabindex='-1' aria-hidden='true'>
        <div class='modal-dialog modal-dialog-centered modal-dialog-scrollable'>
            <div class='modal-content border-0 shadow'>
                <div class='modal-header' style='background:var(--purple-700);color:#fff;'>
                    <h6 class='modal-title fw-bold'>เอกสาร — {$row->lastmachine_no}</h6>
                    <button type='button' class='btn-close btn-close-white' data-bs-dismiss='modal'></button>
                </div>
                <div class='modal-body'>$doc_links$ap_pic_section</div>
            </div>
        </div>
    </div>";

    $rmid = 'removeModal' . $row->id;
    $cancel_html = '';
    if ((int) $row->status === 1) {
        $cancel_html = "<a href='#' style='color:#ef4444;font-size:1.2rem;' data-bs-toggle='modal' data-bs-target='#$rmid'>🗑</a>
        <div class='modal fade' id='$rmid' tabindex='-1' aria-hidden='true'>
            <div class='modal-dialog modal-dialog-centered'>
                <div class='modal-content border-0 shadow'>
                    <div class='modal-body text-center py-4'><div style='font-size:2.5rem;'>⚠️</div><h6 class='mt-2 fw-bold'>ต้องการยกเลิกรายการนี้?</h6></div>
                    <div class='modal-footer border-0 justify-content-center gap-2'>
                        <button class='btn-purple btn' onclick='removelist({$row->id})'>ยืนยัน</button>
                        <button class='btn-outline-purple btn' data-bs-dismiss='modal'>ย้อนกลับ</button>
                    </div>
                </div>
            </div>
        </div>";
    }

    // เอกสารจ่าย: show ap_no only when payment date has passed
    $ap_no_disp = '';
    if (!$ap_no_empty) {
        if ($update_thaidate > $row->ap_date || ($update_thaidate == $row->ap_date && $time_h >= 17))
            $ap_no_disp = htmlspecialchars($row->ap_no);
    }

    // จำนวนเงิน (ap_total)
    $amount_disp = '';
    if (!empty($row->ap_total)) {
        $amount_disp = number_format((float) $row->ap_total, 2) . ' ฿';
    }

    $data[] = [
        $date_disp,
        htmlspecialchars($row->branchno),
        htmlspecialchars($row->lastmachine_no),
        htmlspecialchars($row->customername),
        $btn_file,
        $status_html,
        $ap_no_disp,
        $ap_date_disp,
        $amount_disp,
        '<span style="color:#ef4444;">' . htmlspecialchars($row->comment ?? '') . '</span>',
        $cancel_html,
    ];
}

include("./config/ssDBclose.php");
header('Content-Type: application/json');
echo json_encode(['draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $data]);