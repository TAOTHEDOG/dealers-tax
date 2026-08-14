<?php
session_start();

if ((!isset($_SESSION) || count($_SESSION) == 0) && $_SESSION['role'] != "dealer") {
    header("location: login.php");
    exit;
}

include("./config/config.php");
include("./config/ssDB.php");

$branchgroup = $_SESSION['branchgroup'];

$sql_branch = "select branchcode as cmpcode from branch where etax_group = '$branchgroup'";
$q_branch   = pg_query($connection, $sql_branch);
$branchList = [];
while ($r = pg_fetch_object($q_branch)) {
    $branchList[] = "'" . pg_escape_string($connections, $r->cmpcode) . "'";
}
$branchno = implode(',', $branchList);

$total_count   = 0;
$pending_count = 0;
if (!empty($branchList)) {
    $r1 = pg_query($connections, "SELECT COUNT(*) FROM items WHERE branchno IN ($branchno)");
    $total_count = (int)pg_fetch_result($r1, 0, 0);

    $r2 = pg_query($connections, "SELECT COUNT(*) FROM items WHERE branchno IN ($branchno) AND status = '1'");
    $pending_count = (int)pg_fetch_result($r2, 0, 0);
}

include("./config/ssDBclose.php");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SJ Dealer App — รายการข้อมูล</title>
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="assets/css/bootstrap.min.css">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet">
    <link href="./style/style.css?v=3" rel="stylesheet">
</head>
<body>

<div class="d-flex" style="min-height:100vh;">
    <?php include("sidebar.php") ?>

    <div class="main-content">
        <!-- Topbar -->
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
            <!-- Stat cards -->
            <div class="row g-3 mb-4">
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card card p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon">📋</div>
                            <div>
                                <div class="stat-label">จำนวนทั้งหมด</div>
                                <div class="stat-value"><?php echo $total_count; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-lg-3">
                    <div class="stat-card card p-3">
                        <div class="d-flex align-items-center gap-3">
                            <div class="stat-icon" style="background:#fef3c7; color:#d97706;">⏳</div>
                            <div>
                                <div class="stat-label">รอดำเนินการ (Pending)</div>
                                <div class="stat-value" style="color:#d97706;"><?php echo $pending_count; ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Table card -->
            <div class="table-container">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0" style="color:var(--purple-700);">รายการทั้งหมด</h6>
                    <a href="form.php" class="btn-purple btn btn-sm">+ เพิ่มรายการ</a>
                </div>

                <div class="table-responsive">
                    <table id="myTable" class="display" style="min-width:1300px; width:100%;">
                        <thead>
                            <tr>
                                <th>วันที่บันทึก</th>
                                <th>สาขา</th>
                                <th>เลขถัง</th>
                                <th>ชื่อ-นามสกุลลูกค้า</th>
                                <th>ไฟล์แนบ</th>
                                <th>สถานะ</th>
                                <th>วันที่จ่าย</th>
                                <th>เอกสารจ่าย</th>
                                <th>จำนวนเงิน</th>
                                <th>หมายเหตุ</th>
                                <th>ยกเลิก</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Populated via server-side AJAX -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="assets/js/bootstrap.min.js"></script>
<script src="https://code.jquery.com/jquery-3.7.0.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script>
$(document).ready(function () {
    var table = $("#myTable").DataTable({
        serverSide: true,
        processing: true,
        ajax: {
            url: 'ajax_view.php',
            type: 'POST'
        },

        scrollX: true,

        columns: [
            { data: 0,  width: '100px' },  // วันที่บันทึก
            { data: 1,  width: '60px'  },  // สาขา
            { data: 2,  width: '90px'  },  // เลขถัง
            { data: 3,  width: '160px' },  // ชื่อลูกค้า
            { data: 4,  width: '90px',  orderable: false },  // ไฟล์แนบ
            { data: 5,  width: '95px'  },  // สถานะ
            { data: 6,  width: '110px', },  // วันที่จ่าย
            { data: 7,  width: '95px',  },  // เลขที่เอกสารจ่าย
            { data: 8,  width: '100px', },  // จำนวนเงิน
            { data: 9                  },  // หมายเหตุ
            { data: 10, width: '60px',  orderable: false },  // ยกเลิก
        ],

        order: [[0, 'desc']],
        pageLength: 25,
        lengthMenu: [10, 25, 50, 100],

        createdRow: function (row) {
            $(row).find('.modal').each(function () {
                $(this).appendTo('body');
            });
        },

        dom: '<"d-flex justify-content-between align-items-center mb-3"f>rtip',

        language: {
            search: 'ค้นหา:',
            lengthMenu: 'แสดง _MENU_ รายการ',
            info: 'แสดง _START_–_END_ จาก _TOTAL_ รายการ',
            infoEmpty: 'ไม่มีข้อมูล',
            infoFiltered: '(กรองจาก _MAX_ รายการทั้งหมด)',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' },
            zeroRecords: 'ไม่พบข้อมูลที่ตรงกัน',
            loadingRecords: 'กำลังโหลด...',
            processing: '<div class="text-center py-3" style="color:var(--purple-600); font-weight:600;">กำลังโหลดข้อมูล...</div>'
        }
    });
});

function removelist(id) {
    location.href = "removerecord.php?id=" + id;
}
</script>
</body>
</html>
