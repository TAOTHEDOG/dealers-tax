<?php
include("./../config/config.php");
include("./../config/ssDB.php");

session_start();

if (!isset($_SESSION) || (($_SESSION['role'] != "Accountant") && ($_SESSION['role'] != "Admin") && ($_SESSION['role'] != "CS"))) {
    echo "<script>location.href='" . $hostname . "/login.php'</script>";
    return false;
}

// Map status codes to labels
// {
//     $status_map = [
//         '1' => 'Pending', '2' => 'Processing', '3' => 'Processing',
//         '4' => 'Processing', '5' => 'Paid', '6' => 'Reject', '7' => 'Cancel'
//     ];
// }

// Summary counts only (lightweight)
$q_total = pg_query(
    $connections,
    "SELECT COUNT(*) FROM items
     WHERE ischeck = FALSE AND status <> '7'
     AND (tax_no <> 'TEST      ' OR tax_no IS NULL)"
);
$rowcount = (int) pg_fetch_result($q_total, 0, 0);

// Count items waiting for management (status 1 or 2)
$q_wait = pg_query(
    $connections,
    "SELECT COUNT(*) FROM items
     WHERE (status = '1')
     AND (tax_no <> 'TEST      ' OR tax_no IS NULL)"
);
$item_waitmanage = (int) pg_fetch_result($q_wait, 0, 0);

// Count items in Processing (status 4)
$q_processing = pg_query(
    $connections,
    "SELECT COUNT(*) FROM items
     WHERE status IN ('2', '3')
     AND (tax_no <> 'TEST      ' OR tax_no IS NULL)"
);
$item_processing = (int) pg_fetch_result($q_processing, 0, 0);

// Count items in Paid (status 5)
$q_paid = pg_query(
    $connections,
    "SELECT COUNT(*) FROM items
     WHERE status = '5'
     AND (tax_no <> 'TEST      ' OR tax_no IS NULL)"
);
$item_paid = (int) pg_fetch_result($q_paid, 0, 0);

// Count items in Reject (status 6)
$q_reject = pg_query(
    $connections,
    "SELECT COUNT(*) FROM items
     WHERE status = '6'
     AND (tax_no <> 'TEST      ' OR tax_no IS NULL)"
);
$item_reject = (int) pg_fetch_result($q_reject, 0, 0);

// Count items in Cancel (status 7)
$q_cancel = pg_query(
    $connections,
    "SELECT COUNT(*) FROM items
     WHERE status = '7'
     AND (tax_no <> 'TEST      ' OR tax_no IS NULL)"
);
$item_cancel = (int) pg_fetch_result($q_cancel, 0, 0);
$init_status = 0;



include("../config/dbcloseconnect.php");
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Tax — รายการข้อมูล</title>
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet">
    <link href="./../style/style.css?v=3" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<style>
  /* Container ใหญ่ด้านนอก */
.status-navbar-wrapper {
    width: 100%;
    overflow-x: auto; /* เผื่อหน้าจอเล็กให้เลื่อนแนวนอนได้ */
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
.nav-tab-item .tab-icon { color: #8b5cf6; }
.nav-tab-item.status-pending { color: #f59e0b; }
.nav-tab-item.status-pending .tab-icon { color: #f59e0b; }

.nav-tab-item.status-processing { color: #3b82f6; }
.nav-tab-item.status-processing .tab-icon { color: #3b82f6; }

.nav-tab-item.status-reject { color: #ef4444; }
.nav-tab-item.status-reject .tab-icon { color: #ef4444; }

.nav-tab-item.status-paid { color: #10b981; }
.nav-tab-item.status-paid .tab-icon { color: #10b981; }

.nav-tab-item.status-cancel { color: #64748b; }
.nav-tab-item.status-cancel .tab-icon { color: #64748b; }

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
    box-shadow: 0 1px 3px rgba(0,0,0,0.08);
}
</style>

<body>

    <?php if (!empty($_REQUEST['message'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: <?php echo json_encode($_REQUEST['message']); ?>,
                showConfirmButton: false,
                timer: 3000
            }).then(function () {
                // window.location.href = <?php echo json_encode($hostname); ?> + '/backend/index.php';
                //window.location.href = <?php echo json_encode($hostname); ?> + '/backend/index.php';

            });
        </script>
    <?php endif; ?>

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
                <?php if ($_SESSION['role'] != "CS"): ?>
                    <div class="ms-auto">
                        <button class="btn-purple btn btn-sm" onclick="updateData()">🔄 Update</button>
                    </div>
                <?php endif; ?>
            </div>

            <div class="content-area">
                <!-- Stat cards -->
                <div class="row g-3 mb-4">
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card card p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon">📋</div>
                                <div>
                                    <div class="stat-label">รายการทั้งหมด (ยังไม่ตรวจ)</div>
                                    <div class="stat-value"><?php echo $rowcount; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6 col-lg-3">
                        <div class="stat-card card p-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="stat-icon" style="background:#fef3c7; color:#d97706;">⏳</div>
                                <div>
                                    <div class="stat-label">รอดำเนินการ</div>
                                    <div class="stat-value" style="color:#d97706;"><?php echo $item_waitmanage; ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Table card -->
                <div class="table-container">
                    <!-- Filter Status Row -->
                    <!-- Status Navigation Bar -->
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
                                <span class="tab-count"><?php echo number_format($rowcount); ?></span>
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
                                <span class="tab-count"><?php echo number_format($item_waitmanage); ?></span>
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
                                <span class="tab-count"><?php echo number_format($item_processing); ?></span>
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
                                <span class="tab-count"><?php echo number_format($item_reject); ?></span>
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
                                <span class="tab-count"><?php echo number_format($item_paid); ?></span>
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
                                <span class="tab-count"><?php echo number_format($item_cancel); ?></span>
                            </button>

                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="myTable" class="display w-100">
                            <thead>
                                <tr>
                                    <th>Dealer</th>
                                    <th>สาขา</th>
                                    <th>วันที่-เวลา</th>
                                    <th>เลขถัง 6 หลัก</th>
                                    <th>ชื่อลูกค้า</th>
                                    <th>จัดการ</th>
                                    <th>สถานะ</th>
                                    <th>วันที่จ่าย</th>
                                    <th>เอกสารจ่าย</th>
                                    <th>จำนวนเงิน</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ── Modals (not inside table) ── -->
    <!-- Check / Processing Modal -->
    <div class="modal fade" id="ResultModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:var(--purple-700); color:#fff;">
                    <h5 class="modal-title fw-bold">Processing</h5>
                </div>
                <div class="modal-body">
                    <h6 class="fw-bold mb-2" style="color:var(--purple-700);">📎 รายการเอกสาร</h6>
                    <div id="data_item" class="mb-3"></div>
                    <hr>
                    <?php if ($_SESSION['role'] != 'CS'): ?>
                        <h6 class="fw-bold mb-2" style="color:var(--purple-700);">🔗 ข้อมูลจาก ERP</h6>
                        <div id="data_shiftsoft"></div>
                    <?php endif; ?>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn-outline-purple btn" data-bs-dismiss="modal">ปิด</button>
                    <button type="button" class="btn-purple btn" style="display:none;" onclick="confirm_submit();"
                        id="submit_select">ยืนยัน</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="CommentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:var(--purple-700); color:#fff;">
                    <h5 class="modal-title fw-bold">Reject Item</h5>
                </div>
                <form class="needs-validation" novalidate method="post" action="reject_item.php" id="reject_item">
                    <div class="modal-body">
                        <h6 class="fw-bold mb-2" style="color:var(--purple-700);">📎 รายการเอกสาร</h6>
                        <div id="data_reject_item" class="mb-3"></div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">รายการเลขถัง</label>
                            <input type="text" class="form-control" id="reject_machineno" name="reject_machineno"
                                readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">ข้อความตอบกลับ <span class="file-required">*</span></label>
                            <textarea class="form-control" id="comment" name="comment"
                                placeholder="ข้อความตอบกลับแจ้ง dealer" rows="3" required></textarea>
                            <div class="invalid-feedback">กรุณาพิมพ์ข้อความตอบกลับ</div>
                        </div>
                        <input type="hidden" id="item_id" name="item_id">
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn-outline-purple btn" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn-reject-action btn">ยืนยัน Reject</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit Machine No Modal -->
    <div class="modal fade" id="EditModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header" style="background:var(--purple-700); color:#fff;">
                    <h5 class="modal-title fw-bold">แก้ไขเลขถัง</h5>
                </div>
                <form class="needs-validation" novalidate method="post" action="editmachineno.php" id="editmachineno"
                    onsubmit="var m=document.getElementById('edit_machineno').value;return confirm('ยืนยันการแก้ไขเลขถัง '+m+' ?');">
                    <div class="modal-body">
                        <h6 class="fw-bold mb-2" style="color:var(--purple-700);">📎 รายการเอกสาร</h6>
                        <div id="data_edit_item" class="mb-3"></div>
                        <hr>
                        <div class="mb-3">
                            <label class="form-label">เลขถังปัจจุบัน</label>
                            <input type="text" class="form-control" id="edit_machineno" name="edit_machineno" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">เลขถังที่ถูกต้อง <span class="file-required">*</span></label>
                            <input class="form-control" id="correct_machine" name="correct_machine"
                                placeholder="ใส่เลขถังที่ถูกต้อง" minlength="17" maxlength="17" required>
                            <div class="invalid-feedback">กรุณาใส่เลขถังที่ถูกต้อง</div>
                        </div>
                        <input type="hidden" id="edit_item_id" name="edit_item_id">
                    </div>
                    <div class="modal-footer border-0">
                        <button type="button" class="btn-outline-purple btn" data-bs-dismiss="modal">ปิด</button>
                        <button type="submit" class="btn-edit-action btn">ยืนยัน</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        var currentStatus = <?php echo json_encode($init_status); ?>;
        console.log("Initial status:", currentStatus);
        $(document).ready(function () {
            var table = $("#myTable").DataTable({
                serverSide: true,
                processing: true,
                stateSave: true,
                stateDuration: -1,
                ajax: {
                    url: 'ajax_index.php',
                    type: 'POST',
                    data: function (d) {
                        d.status = currentStatus;
                    }
                },

                columns: [
                    { data: 0 },                               // Dealer
                    { data: 1, width: '60px' },               // สาขา
                    { data: 2, width: '105px' },               // วันที่
                    { data: 3, width: '80px' },               // เลขถัง
                    { data: 4 },                               // ชื่อลูกค้า
                    { data: 5, orderable: false },             // จัดการ
                    { data: 6, width: '110px' },               // สถานะ
                    { data: 7, width: '100px' },               // วันที่จ่าย
                    { data: 8, width: '110px' },               // เอกสารจ่าย
                    { data: 9, width: '100px' },               // จำนวนเงิน
                ],

                order: [[2, 'desc']],
                pageLength: 25,
                lengthMenu: [10, 25, 50, 100],

                dom: '<"d-flex justify-content-between align-items-center mb-3"Bf>rtip',
                buttons: [
                    { extend: 'excel', text: '⬇ Excel', className: '' },
                    { extend: 'print', text: '🖨 Print', className: '' }
                ],

                language: {
                    search: 'ค้นหา:',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'แสดง _START_–_END_ จาก _TOTAL_ รายการ',
                    infoEmpty: 'ไม่มีข้อมูล',
                    infoFiltered: '(กรองจาก _MAX_ รายการทั้งหมด)',
                    paginate: { first: '«', last: '»', next: '›', previous: '‹' },
                    zeroRecords: 'ไม่พบข้อมูลที่ตรงกัน',
                    loadingRecords: 'กำลังโหลด...',
                    processing: '<div class="text-center py-3" style="color:var(--purple-600);font-weight:600;">กำลังโหลดข้อมูล...</div>'
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
                    if (typeof table !== 'undefined') {
                        table.ajax.reload();
                    }
                });
            });
            // $('.btn-status-filter').on('click', function () {
            //     $('.btn-status-filter').removeClass('btn-purple').addClass('btn-outline-purple');
            //     $(this).removeClass('btn-outline-purple').addClass('btn-purple');

            //     currentStatus = $(this).data('status');
            //     console.log("Current status set to:", currentStatus);
            //     table.ajax.reload();
            // });
        });

        function updateData() {
            location.href = "./updateauto.php";
        }

        var _hostname = <?php echo json_encode($hostname); ?>;

        function show_data_file(id, status) {
            if (status == '1' || status == '2') {
                $.ajax({
                    type: "POST", url: 'checklastmachine_beanchgroup.php', data: { item_id: id },
                    success: function (r) {
                        var d = JSON.parse(r);
                        if (d.ic_no) alert("รายการนี้ถูกรับแล้วในเลขที่ใบรับ : " + d.ic_no);
                    }
                });
            }

            $.ajax({
                type: "POST", url: 'call_chassisnos.php', data: { item_id: id },
                success: function (r) {
                    var d = JSON.parse(r);
                    $('#ResultModal').modal('show');
                    $('#data_item').empty();
                    $('#data_shiftsoft').empty();

                    var itemAdd = '<ul class="list-unstyled">';
                    var docs = { doc1: 'ใบกำกับภาษีค่ารถ', doc2: 'Commission', doc3: 'เอกสารรับเงินดาวน์', doc4: 'ซับดาวน์', doc5: 'ซับงวด' };
                    var n = 1;
                    for (var key in docs) {
                        if (d['item'][key]) {
                            itemAdd += '<li><a href="' + _hostname + '/docs/' + d['item'][key] + '" download class="text-decoration-none" style="color:var(--purple-600);">⬇ ' + docs[key] + '</a></li>';
                        }
                        n++;
                    }
                    itemAdd += '</ul>';
                    $('#data_item').append(itemAdd);

                    if (d['machine_datas'] && d['machine_datas'].length > 0 && status <= '2') {
                        var mds = d['machine_datas'];
                        var form = '<form action="select_item.php" method="POST" id="select_form"><div class="row fw-bold mb-2"><div class="col-3">วันที่</div><div class="col-3">เลขที่ใบรับ</div><div class="col-4">เลขถัง</div><div class="col-2 text-center">เลือก</div></div>';
                        for (var i = 0; i < mds.length; i++) {
                            var icdate = mds[i].icdate || '';
                            var apdate = mds[i].apdate || '';
                            form += '<div class="row mb-2 align-items-center">' +
                                '<div class="col-3"><input class="form-control form-control-sm" value="' + icdate + '" disabled readonly></div>' +
                                '<div class="col-3"><input class="form-control form-control-sm" value="' + mds[i].icno + '" disabled readonly></div>' +
                                '<div class="col-4"><input class="form-control form-control-sm" value="' + mds[i].chassisno + '" disabled readonly></div>' +
                                '<div class="col-2 text-center"><input type="checkbox" class="form-check-input" name="select_item" id="item_' + i + '" value="' + mds[i].chassisno + '" onchange="select_machine(' + i + ')"></div>' +
                                '<input type="hidden" id="apno_' + i + '" value="' + mds[i].apno + '">' +
                                '<input type="hidden" id="apdate_' + i + '" value="' + apdate + '">' +
                                '<input type="hidden" id="icdate_' + i + '" value="' + icdate + '">' +
                                '<input type="hidden" id="icno_' + i + '" value="' + mds[i].icno + '">' +
                                '<input type="hidden" id="machineno_' + i + '" value="' + mds[i].chassisno + '"></div>';
                        }
                        form += '<input type="hidden" id="select_icdate" name="select_icdate">' +
                            '<input type="hidden" id="select_icno" name="select_icno">' +
                            '<input type="hidden" id="select_machineno" name="select_machineno">' +
                            '<input type="hidden" id="select_apno" name="select_apno">' +
                            '<input type="hidden" id="select_apdate" name="select_apdate">' +
                            '<input type="hidden" id="item_id" name="item_id" value="' + d['item']['id'] + '">' +
                            '<input type="hidden" id="lastmachine_no" name="lastmachine_no" value="' + d['item']['lastmachine_no'] + '"></form>';
                        $('#data_shiftsoft').append(form);
                        document.getElementById('submit_select').style.display = 'inline-block';
                    }
                }
            });
        }

        function select_machine(i) {
            if (document.getElementById('item_' + i).checked) {
                document.getElementById('select_machineno').value = document.getElementById('machineno_' + i).value;
                document.getElementById('select_icdate').value = document.getElementById('icdate_' + i).value;
                document.getElementById('select_icno').value = document.getElementById('icno_' + i).value;
                document.getElementById('select_apdate').value = document.getElementById('apdate_' + i).value;
                document.getElementById('select_apno').value = document.getElementById('apno_' + i).value;
            } else {
                ['select_machineno', 'select_icdate', 'select_icno', 'select_apdate', 'select_apno'].forEach(function (id) { document.getElementById(id).value = ''; });
            }
        }

        function confirm_submit() {
            Swal.fire({
                title: 'ยืนยันการทำรายการ?', icon: 'warning', showCancelButton: true,
                confirmButtonText: 'ยืนยัน!', cancelButtonText: 'ปิด',
                confirmButtonColor: '#6D28D9', cancelButtonColor: '#d33'
            }).then(function (r) { if (r.isConfirmed) $('#select_form').submit(); });
        }

        function confirm_comment(item_id) {
            $.ajax({
                type: "POST", url: 'confirm_comment.php', data: { item_id: item_id },
                success: function (r) {
                    var d = JSON.parse(r);
                    $('#data_reject_item').empty();
                    $('#CommentModal').modal('show');
                    var itemAdd = '<ul class="list-unstyled">';
                    var docs = { doc1: 'ใบกำกับภาษีค่ารถ', doc2: 'Commission', doc3: 'เอกสารรับเงินดาวน์', doc4: 'ซับดาวน์', doc5: 'ซับงวด' };
                    for (var k in docs) { if (d['item'][k]) itemAdd += '<li><a href="' + _hostname + '/docs/' + d['item'][k] + '" download style="color:var(--purple-600);">⬇ ' + docs[k] + '</a></li>'; }
                    itemAdd += '</ul>';
                    $('#data_reject_item').append(itemAdd);
                    $('#reject_machineno').val(d['item']['lastmachine_no']);
                    $('#item_id').val(d['item']['id']);
                }
            });
        }

        function edit_machineno(item_id) {
            $.ajax({
                type: "POST", url: 'edit_machineno.php', data: { item_id: item_id },
                success: function (r) {
                    var d = JSON.parse(r);
                    $('#data_edit_item').empty();
                    $('#EditModal').modal('show');
                    var itemAdd = '<ul class="list-unstyled">';
                    var docs = { doc1: 'ใบกำกับภาษีค่ารถ', doc2: 'Commission', doc3: 'เอกสารรับเงินดาวน์', doc4: 'ซับดาวน์', doc5: 'ซับงวด' };
                    for (var k in docs) { if (d['item'][k]) itemAdd += '<li><a href="' + _hostname + '/docs/' + d['item'][k] + '" download style="color:var(--purple-600);">⬇ ' + docs[k] + '</a></li>'; }
                    itemAdd += '</ul>';
                    $('#data_edit_item').append(itemAdd);
                    $('#edit_machineno').val(d['item']['lastmachine_no']);
                    $('#edit_item_id').val(d['item']['id']);
                }
            });
        }

        // Bootstrap validation for modals
        (function () {
            'use strict';
            document.querySelectorAll('.needs-validation').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
                    form.classList.add('was-validated');
                }, false);
            });
        })();
        document.getElementById('reject_item').addEventListener('submit', function (e) {
            if (!this.checkValidity()) {
                e.preventDefault();
                this.classList.add('was-validated');
                return;
            }
            // ล็อคปุ่มทันทีที่กด เพื่อป้องกันการกดซ้ำ
            const btn = this.querySelector('button[type="submit"]');
            btn.disabled = true;
            btn.innerText = 'กำลังบันทึก...';
        });
    </script>
</body>

</html>