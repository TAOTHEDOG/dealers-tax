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
$q_branch = pg_query($connection, $sql_branch);
$branchList = [];
while ($r = pg_fetch_object($q_branch)) {
    $branchList[] = "'" . pg_escape_string($connections, $r->cmpcode) . "'";
}
$branchno = implode(',', $branchList);

$total_count = 0;
$pending_count = 0;
if (!empty($branchList)) {
    $r1 = pg_query($connections, "SELECT COUNT(*) FROM items WHERE branchno IN ($branchno)");
    $total_count = (int) pg_fetch_result($r1, 0, 0);

    $r2 = pg_query($connections, "SELECT COUNT(*) FROM items WHERE branchno IN ($branchno) AND status = '1'");
    $pending_count = (int) pg_fetch_result($r2, 0, 0);
}



// Count items in Processing (status 4)
$q_processing = pg_query(
    $connections,
    "SELECT COUNT(*) FROM items
     WHERE status IN ('4') and branchno IN ($branchno)
     AND (tax_no <> 'TEST      ' OR tax_no IS NULL)"
);
$processing_count = (int) pg_fetch_result($q_processing, 0, 0);

// Count items in Paid (status 5)
$q_paid = pg_query(
    $connections,
    "SELECT COUNT(*) FROM items
     WHERE status = '5' and branchno IN ($branchno)
     AND (tax_no <> 'TEST      ' OR tax_no IS NULL)"
);
$paid_count = (int) pg_fetch_result($q_paid, 0, 0);

// Count items in Reject (status 6)
$q_reject = pg_query(
    $connections,
    "SELECT COUNT(*) FROM items
     WHERE status = '6' and branchno IN ($branchno)
     AND (tax_no <> 'TEST      ' OR tax_no IS NULL)"
);
$reject_count = (int) pg_fetch_result($q_reject, 0, 0);

// Count items in Cancel (status 7)
$q_cancel = pg_query(
    $connections,
    "SELECT COUNT(*) FROM items
     WHERE status = '7' and branchno IN ($branchno)
     AND (tax_no <> 'TEST      ' OR tax_no IS NULL)"
);
$cancel_count = (int) pg_fetch_result($q_cancel, 0, 0);

$init_status = 0;

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

<style>
    /* Container ใหญ่ด้านนอก */
    .status-navbar-wrapper {
        width: 100%;
        overflow-x: auto;
        /* เผื่อหน้าจอเล็กให้เลื่อนแนวนอนได้ */
        border-bottom: 1px solid #eef0f6;
        background: #ffffff;
    }

    .status-navbar {
        display: flex;
        align-items: center;
        gap: 0;
        padding: 0;
    }

    /* แต่ละ Tap Item */
    .nav-tab-item {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 12px 20px;
        background: transparent;
        border: none;
        border-bottom: 2px solid transparent;
        cursor: pointer;
        font-size: 0.92rem;
        font-weight: 600;
        color: #64748b;
        transition: all 0.2s ease-in-out;
        white-space: nowrap;
        position: relative;
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
    }

    .nav-tab-item:hover {
        background-color: #f8fafc;
    }

    /* ตัวเลขสถิติ Pill Badge ด้านขวา */
    .tab-count {
        background-color: #f1f5f9;
        color: #475569;
        padding: 3px 10px;
        border-radius: 12px;
        font-size: 0.8rem;
        font-weight: 600;
        margin-left: 4px;
    }

    /* เส้นกั้นแบ่งระหว่างปุ่ม */
    .tab-divider {
        width: 1px;
        height: 20px;
        background-color: #e2e8f0;
        margin: 0 4px;
    }

    /* สีของข้อความและไอคอนแต่ละสถานะ */
    .nav-tab-item .tab-icon {
        color: #8b5cf6;
    }

    .nav-tab-item.status-pending {
        color: #f59e0b;
    }

    .nav-tab-item.status-pending .tab-icon {
        color: #f59e0b;
    }

    .nav-tab-item.status-processing {
        color: #3b82f6;
    }

    .nav-tab-item.status-processing .tab-icon {
        color: #3b82f6;
    }

    .nav-tab-item.status-reject {
        color: #ef4444;
    }

    .nav-tab-item.status-reject .tab-icon {
        color: #ef4444;
    }

    .nav-tab-item.status-paid {
        color: #10b981;
    }

    .nav-tab-item.status-paid .tab-icon {
        color: #10b981;
    }

    .nav-tab-item.status-cancel {
        color: #64748b;
    }

    .nav-tab-item.status-cancel .tab-icon {
        color: #64748b;
    }

    /* Active State (สถานะที่กำลังถูกเลือกอยู่) */
    .nav-tab-item.active {
        background-color: #f5f3ff !important;
        border-bottom-color: #7c3aed !important;
        color: #6d28d9 !important;
    }

    .nav-tab-item.active .tab-icon {
        color: #6d28d9 !important;
    }

    .nav-tab-item.active .tab-count {
        background-color: #ffffff;
        color: #6d28d9;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
</style>

<body>

    <div class="d-flex" style="min-height:100vh;">
        <?php include("sidebar.php") ?>

        <div class="main-content">
            <!-- Topbar -->
            <div class="navbar-topbar">
                <button class="border-0 bg-transparent" data-bs-target="#sidebar" data-bs-toggle="collapse"
                    style="color:var(--purple-600); cursor:pointer;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor"
                        viewBox="0 0 16 16">
                        <path fill-rule="evenodd"
                            d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z" />
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


                    <div class="status-navbar-wrapper mb-4">
                        <div class="status-navbar">
                            <!-- ทั้งหมด -->
                            <button type="button"
                                class="nav-tab-item <?php echo $init_status == 'all' ? 'active' : ''; ?>"
                                data-status="all">
                                <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <span class="tab-text">ทั้งหมด</span>
                                <span class="tab-count"><?php echo number_format($total_count); ?></span>
                            </button>


                            <div class="tab-divider"></div>
                            <!-- Pending (รอดำเนินการ) -->
                            <button type="button"
                                class="nav-tab-item status-pending <?php echo $init_status == '1' ? 'active' : ''; ?>"
                                data-status="1">
                                <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="tab-text">Pending</span>
                                <span class="tab-count"><?php echo number_format($pending_count); ?></span>
                            </button>

                            <div class="tab-divider"></div>

                            <!-- Processing -->
                            <button type="button"
                                class="nav-tab-item status-processing <?php echo $init_status == '2' ? 'active' : ''; ?>"
                                data-status="2">
                                <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="tab-text">Processing</span>
                                <span class="tab-count"><?php echo number_format($processing_count); ?></span>
                            </button>

                            <div class="tab-divider"></div>

                            <!-- Reject -->
                            <button type="button"
                                class="nav-tab-item status-reject <?php echo $init_status == '6' ? 'active' : ''; ?>"
                                data-status="6">
                                <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="tab-text">Reject</span>
                                <span class="tab-count"><?php echo number_format($reject_count); ?></span>
                            </button>

                            <div class="tab-divider"></div>

                            <!-- Paid -->
                            <button type="button"
                                class="nav-tab-item status-paid <?php echo $init_status == '3' || $init_status == '5' ? 'active' : ''; ?>"
                                data-status="3">
                                <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span class="tab-text">Paid</span>
                                <span class="tab-count"><?php echo number_format($paid_count); ?></span>
                            </button>

                            <div class="tab-divider"></div>

                            <!-- Cancel -->
                            <button type="button"
                                class="nav-tab-item status-cancel <?php echo $init_status == '7' ? 'active' : ''; ?>"
                                data-status="7">
                                <svg class="tab-icon" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                <span class="tab-text">Cancel</span>
                                <span class="tab-count"><?php echo number_format($cancel_count); ?></span>
                            </button>

                        </div>
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
      var currentStatus = <?php echo json_encode($init_status); ?>

      
        $(document).ready(function () {
            var table = $("#myTable").DataTable({
                serverSide: true,
                processing: true,
                ajax: {
                    url: 'ajax_view.php',
                    type: 'POST',
                    data: function (d) {
                        d.status = currentStatus;
                    }
                },
                scrollX: true,

                columns: [
                    { data: 0, width: '100px' },  // วันที่บันทึก
                    { data: 1, width: '60px' },  // สาขา
                    { data: 2, width: '90px' },  // เลขถัง
                    { data: 3, width: '160px' },  // ชื่อลูกค้า
                    { data: 4, width: '90px', orderable: false },  // ไฟล์แนบ
                    { data: 5, width: '95px' },  // สถานะ
                    { data: 6, width: '110px', },  // วันที่จ่าย
                    { data: 7, width: '95px', },  // เลขที่เอกสารจ่าย
                    { data: 8, width: '100px', },  // จำนวนเงิน
                    { data: 9 },  // หมายเหตุ
                    { data: 10, width: '60px', orderable: false },  // ยกเลิก
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


            // Filter button click handler
            $(document).ready(function () {
                $('.nav-tab-item').on('click', function () {
                    // เอา class active ออกจากทุกปุ่ม แล้วใส่ให้ปุ่มที่กด
                    $('.nav-tab-item').removeClass('active');
                    $(this).addClass('active');

                    // ดึงค่า status แล้วสั่งรีโหลด DataTables
                    currentStatus = $(this).data('status');
                    console.log("Selected status:", currentStatus);
                    if (typeof table !== 'undefined') {
                        table.ajax.reload();
                    }
                });
            });
        });

        function removelist(id) {
            location.href = "removerecord.php?id=" + id;
        }
    </script>
</body>

</html>