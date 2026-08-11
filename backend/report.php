<?php
include("./../config/config.php");
session_start();
if ((!isset($_SESSION) || count($_SESSION) == 0) && $_SESSION['role'] != "admin") {
    header("location: ./../login.php");
    return false;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SJ Dealer App — รายงาน</title>
    <link rel="icon" type="image/x-icon" href="./../assets/images/favicon.ico">
    <link rel="stylesheet" type="text/css" href="./../assets/css/bootstrap.min.css">
    <link href="./../style/style.css?v=2" rel="stylesheet">
    <script type="text/javascript" src="./../assets/js/bootstrap.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
</head>
<body>

<div class="d-flex" style="min-height:100vh;">
    <?php include("sidebar.php") ?>

    <div class="main-content">
        <!-- Topbar -->
        <div class="navbar-topbar">
            <button class="border-0 bg-transparent" style="color:var(--purple-600);cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </button>
            <span class="dealer-name">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                    <path d="M14 1a1 1 0 0 1 1 1v8a1 1 0 0 1-1 1H4.414A2 2 0 0 0 3 11.586l-2 2V2a1 1 0 0 1 1-1h12zM2 0a2 2 0 0 0-2 2v12.793a.5.5 0 0 0 .854.353l2.853-2.853A1 1 0 0 1 4.414 12H14a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H2z"/>
                </svg>
                รายงาน
            </span>
        </div>

        <!-- Content -->
        <div class="content-area">
            <div class="page-card">
                <div class="section-title">
                    📊 ออกรายงาน
                </div>

                <div class="row g-4">
                    <!-- Report list -->
                    <div class="col-md-4">
                        <label class="form-label mb-2">เลือกประเภทรายงาน</label>
                        <div class="report-list">
                            <div class="report-list-item selected" data-report="report1" data-label="รายงานส่งใบกำกับภาษี">
                                <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" fill="currentColor" class="me-2" viewBox="0 0 16 16">
                                    <path d="M14 4.5V14a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V2a2 2 0 0 1 2-2h5.5L14 4.5zm-3 0A1.5 1.5 0 0 1 9.5 3V1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V4.5h-2z"/>
                                </svg>
                                รายงานส่งใบกำกับภาษี
                            </div>
                        </div>
                    </div>

                    <!-- Report form -->
                    <div class="col-md-8">
                        <div style="background:var(--purple-50); border-radius:12px; padding:1.5rem; border:1.5px solid var(--purple-100);">
                            <div class="mb-3">
                                <label class="form-label">ชื่อรายงาน</label>
                                <input type="text" id="reportname" class="form-control" disabled
                                       value="รายงานส่งใบกำกับภาษี"
                                       style="background:#fff; color:var(--purple-700); font-weight:600;">
                            </div>
                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label">รหัสสาขาตั้งแต่</label>
                                    <input type="text" id="branchfrom" class="form-control" placeholder="เช่น 0001">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">รหัสสาขาถึง</label>
                                    <input type="text" id="branchto" class="form-control" placeholder="เช่น 9999">
                                </div>
                            </div>
                            <div class="row g-3 mb-4">
                                <div class="col-sm-6">
                                    <label class="form-label">วันที่ตั้งแต่ <span class="file-required">*</span></label>
                                    <input type="date" id="datefrom" class="form-control" required>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label">วันที่ถึง <span class="file-required">*</span></label>
                                    <input type="date" id="dateto" class="form-control" required>
                                </div>
                            </div>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn-purple btn" id="btnGenReport">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="me-1" viewBox="0 0 16 16">
                                        <path d="M.5 9.9a.5.5 0 0 1 .5.5v2.5a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1v-2.5a.5.5 0 0 1 1 0v2.5a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2v-2.5a.5.5 0 0 1 .5-.5z"/>
                                        <path d="M7.646 11.854a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V1.5a.5.5 0 0 0-1 0v8.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3z"/>
                                    </svg>
                                    ออกรายงาน
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Validation Modal -->
<div class="modal fade" id="validationModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body text-center py-4">
                <div style="font-size:2.5rem;">⚠️</div>
                <h6 class="mt-2 fw-bold" style="color:var(--purple-700);">กรุณากรอกข้อมูลให้ครบถ้วน</h6>
                <p class="text-muted mb-0" style="font-size:.875rem;">โปรดระบุวันที่ตั้งแต่และวันที่ถึง</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn-purple btn" data-bs-dismiss="modal">ตกลง</button>
            </div>
        </div>
    </div>
</div>

<script>
var selectedReport = 'report1';

$('.report-list-item').on('click', function () {
    $('.report-list-item').removeClass('selected');
    $(this).addClass('selected');
    selectedReport = $(this).data('report');
    $('#reportname').val($(this).data('label'));
});

$('#btnGenReport').on('click', function () {
    var datefrom  = $('#datefrom').val();
    var dateto    = $('#dateto').val();
    var branchfrom = $('#branchfrom').val();
    var branchto   = $('#branchto').val();

    if (!datefrom || !dateto) {
        new bootstrap.Modal(document.getElementById('validationModal')).show();
        return;
    }

    var url = './report/' + selectedReport + '.php'
            + '?branchfrom=' + encodeURIComponent(branchfrom)
            + '&branchto='   + encodeURIComponent(branchto)
            + '&datefrom='   + encodeURIComponent(datefrom)
            + '&dateto='     + encodeURIComponent(dateto);
    window.location.href = url;
});
</script>
</body>
</html>
