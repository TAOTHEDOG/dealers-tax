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

$draw   = intval($_POST['draw']   ?? 1);
$start  = intval($_POST['start']  ?? 0);
$length = intval($_POST['length'] ?? 25);
$search = pg_escape_string($connections, $_POST['search']['value'] ?? '');

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

// Base filter: not checked, not cancelled, no test records
$base_filter = "items.ischeck = FALSE
                AND items.status <> '7'
                AND (items.tax_no <> 'TEST      ' OR items.tax_no IS NULL)";

$search_filter = '';
if ($search !== '') {
    $search_filter = " AND (items.lastmachine_no ILIKE '%$search%'
                        OR items.customername ILIKE '%$search%'
                        OR items.branchno ILIKE '%$search%'
                        OR users.dealer ILIKE '%$search%')
                        OR items.created_at::varchar  ILIKE '%$search%'
                        OR items.ap_date::varchar  ILIKE '%$search%'
                        ";
}

$join = "FROM items LEFT JOIN users ON items.dealer_id = users.id";

// Total (unfiltered)
$r_total = pg_query($connections, "SELECT COUNT(*) $join WHERE $base_filter");
$total   = (int)pg_fetch_result($r_total, 0, 0);

// Filtered count
$r_filt   = pg_query($connections, "SELECT COUNT(*) $join WHERE $base_filter $search_filter");
$filtered = (int)pg_fetch_result($r_filt, 0, 0);

// Data
$query  = "SELECT items.*, users.dealer, users.name, users.branchgroup
           $join
           WHERE $base_filter $search_filter
           ORDER BY $orderCol $orderDir
           LIMIT $length OFFSET $start";
$result = pg_query($connections, $query);

$is_cs    = ($_SESSION['role'] == "CS");
$is_admin = ($_SESSION['role'] == "Admin");
$hostname = $hostname ?? '';

$data = [];
while ($row = pg_fetch_object($result)) {

    // Date display
    $dt       = new DateTime($row->created_at);
    $ex       = explode('-', $dt->format('Y-m-d'));
    $disp_d   = (intval($ex[0]) + 543) . '-' . $ex[1] . '-' . $ex[2];

    // Status label
    $status_map = [
        '1' => 'Pending', '2' => 'Processing', '3' => 'Processing',
        '4' => 'Edit machineno', '5' => 'Paid', '6' => 'Reject',
    ];
    $status_label = $status_map[$row->status] ?? '—';

    $badge_class = [
        'Pending'         => 'badge-pending',
        'Processing'      => 'badge-processing',
        'Edit machineno'  => 'badge-pending',
        'Paid'            => 'badge-paid',
        'Reject'          => 'badge-reject',
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

    // ap_date display
    $ap_date_disp = ($row->status == '5') ? htmlspecialchars($row->ap_date ?? '') : '';

    $data[] = [
        htmlspecialchars($row->dealer ?? ''),
        htmlspecialchars($row->branchno),
        $disp_d,
        htmlspecialchars($row->lastmachine_no),
        htmlspecialchars($row->customername),
        $actions,
        $status_html,
        $ap_date_disp,
    ];
}

include("./../config/ssDBclose.php");

header('Content-Type: application/json');
echo json_encode([
    'draw'            => $draw,
    'recordsTotal'    => $total,
    'recordsFiltered' => $filtered,
    'data'            => $data,
]);
