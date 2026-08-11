<?php
session_start();
if ((!isset($_SESSION) || count($_SESSION) == 0) || $_SESSION['role'] != "Dealer") {
    header("location: login.php");
    exit;
}
include("./config/config.php");
include("./config/ssDB.php");

$branchgroup = $_SESSION['branchgroup'];
$sql_branch  = "select branchcode as cmpcode from branch where etax_group = '$branchgroup'";
$q_branch    = pg_query($connection, $sql_branch);
$branchList  = [];
while ($r = pg_fetch_object($q_branch)) {
    $branchList[] = "'" . pg_escape_string($connections, $r->cmpcode) . "'";
}
include("./config/ssDBclose.php");

$datefrom = trim($_GET['datefrom'] ?? '');
$dateto   = trim($_GET['dateto']   ?? '');

if ($datefrom && $dateto && !empty($branchList)) {
    $branchno = implode(',', $branchList);
    $df = pg_escape_string($connections, $datefrom);
    $dt = pg_escape_string($connections, $dateto);

    $result = pg_query($connections,
        "SELECT created_at, branchno, lastmachine_no, customername, ap_no, ap_date, ap_total, status, comment
         FROM items
         WHERE branchno IN ($branchno)
           AND DATE(created_at) BETWEEN '$df' AND '$dt'
         ORDER BY created_at DESC"
    );

    include("./config/dbcloseconnect.php");

    $status_map = [
        '1' => 'Pending', '2' => 'Processing', '3' => 'Processing',
        '4' => 'Processing', '5' => 'Paid', '6' => 'Reject', '7' => 'Cancel'
    ];

    $rows_data = [];
    while ($row = pg_fetch_assoc($result)) {
        $yr        = ((int)substr($row['created_at'], 0, 4)) + 543;
        $row['date_disp'] = $yr . substr($row['created_at'], 4, 7);
        $row['amount']    = !empty($row['ap_total']) ? number_format((float)$row['ap_total'], 2) : '';
        $row['status_label'] = $status_map[$row['status']] ?? '';
        $rows_data[] = $row;
    }

    $filename = 'report_' . $datefrom . '_' . $dateto . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<head><meta charset="UTF-8"><meta http-equiv="Content-Type" content="text/html; charset=UTF-8"></head>
<body>
<table border="1">
    <thead>
        <tr>
            <th>#</th>
            <th>วันที่บันทึก</th>
            <th>สาขา</th>
            <th>เลขถัง</th>
            <th>ชื่อลูกค้า</th>
            <th>เลขที่ AP</th>
            <th>วันที่จ่าย</th>
            <!-- <th>จำนวนเงิน</th> -->
            <th>สถานะ</th>
            <th>หมายเหตุ</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($rows_data as $i => $row): ?>
        <tr>
            <td><?php echo $i + 1; ?></td>
            <td><?php echo htmlspecialchars($row['date_disp']); ?></td>
            <td><?php echo htmlspecialchars($row['branchno']); ?></td>
            <td><?php echo htmlspecialchars($row['lastmachine_no']); ?></td>
            <td><?php echo htmlspecialchars($row['customername']); ?></td>
            <td><?php echo htmlspecialchars($row['ap_no'] ?? ''); ?></td>
            <td><?php echo htmlspecialchars($row['ap_date'] ?? ''); ?></td>
            <!-- <td><?php echo $row['amount']; ?></td> -->
            <td><?php echo htmlspecialchars($row['status_label']); ?></td>
            <td><?php echo htmlspecialchars($row['comment'] ?? ''); ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
</body>
</html>
<?php
    exit;
}

include("./config/dbcloseconnect.php");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dealer's Tax — รายงาน</title>
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link href="./style/style.css?v=3" rel="stylesheet">
    <script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
</head>
<body>

<div class="d-flex" style="min-height:100vh;">
    <?php include("sidebar.php") ?>

    <div class="main-content">
        <div class="navbar-topbar">
            <button class="border-0 bg-transparent" data-bs-target="#sidebar" data-bs-toggle="collapse"
                    style="color:var(--purple-600); cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </button>
            <span class="dealer-name"><?php echo htmlspecialchars($_SESSION['dealer'] ?? ''); ?></span>
        </div>

        <div class="content-area">
            <div class="page-card">
                <div class="section-title">📊 รายงาน</div>

                <form method="GET" action="report.php" class="row g-3">
                    <div class="col-sm-4">
                        <label class="form-label">วันที่ตั้งแต่ <span class="file-required">*</span></label>
                        <input type="date" name="datefrom" class="form-control" required>
                    </div>
                    <div class="col-sm-4">
                        <label class="form-label">วันที่ถึง <span class="file-required">*</span></label>
                        <input type="date" name="dateto" class="form-control" required>
                    </div>
                    <div class="col-sm-4 d-flex align-items-end">
                        <button type="submit" class="btn-purple btn w-100">⬇ ดาวน์โหลด Excel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

</body>
</html>
