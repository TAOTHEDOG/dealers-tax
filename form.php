<?php include("./config/config.php"); ?>
<?php include("./config/ssDB.php"); ?>
<?php
session_start();
if ((!isset($_SESSION) || count($_SESSION) == 0) && $_SESSION['role'] != "Dealer") {
    header("location: login.php");
    return false;
}
$branchgroup = $_SESSION['branchgroup'];
?>
<!DOCTYPE html>
<html lang="th">
<!-- <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SJ Dealer App — เพิ่มข้อมูล</title>
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="./style/style.css?v=2" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
</head> -->
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SJ Dealer App — เพิ่มข้อมูล</title>
    
    <!-- เปลี่ยน ./ เป็น / นำหน้าสำหรับ assets และ styles -->
    <link rel="icon" type="image/x-icon" href="/assets/images/favicon.ico">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS: เปลี่ยนเป็น Absolute path / และเพิ่ม Query param เพื่อล้างแคช -->
    <link href="/style/style.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- หมายเหตุ: ถ้าไม่ได้ใช้ PHP ให้เปลี่ยน ?v=2 เป็น ?v=3 หรือเลขใหม่ไปเรื่อยๆ เมื่อแก้ไขไฟล์ CSS -->

    <!-- JS Scripts (แนะนำย้ายไปไว้ก่อนปิด </body> หากต้องการให้โหลดหน้าไวขึ้น แต่ถ้าจำเป็นไว้ใน head ให้ใส่ defer ไว้) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</head>
<body>

<div class="loading-overlay" id="loadingOverlay" style="display:none;">
    <span class="loader"></span>
    <span>กำลังบันทึกข้อมูล...</span>
</div>

<div class="d-flex" style="min-height:100vh;">
    <?php include("sidebar.php") ?>
    <div class="main-content">
        <div class="navbar-topbar">
            <button class="border-0 bg-transparent" data-bs-target="#sidebar" data-bs-toggle="collapse"
                    style="color:var(--purple-600);cursor:pointer;">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5zm0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5z"/>
                </svg>
            </button>
            <span class="dealer-name"><?php echo htmlspecialchars($_SESSION['dealer'] ?? ''); ?></span>
        </div>

        <div class="content-area">
            <div class="page-card">
                <div class="section-title">➕ เพิ่มข้อมูลรายการ</div>
                <form action="insert_item.php" method="POST" enctype="multipart/form-data" id="submititem">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">หมายเลขตัวถัง <span class="file-required">*</span></label>
                            <input type="number" id="lastmachine_no" name="machine_no"
                                   onKeyPress="if(this.value.length==6) return false;"
                                   class="form-control" placeholder="กรอก 6 หลัก" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">ชื่อ-นามสกุลลูกค้า <span class="file-required">*</span></label>
                            <input type="text" id="customername" class="form-control"
                                   placeholder="กรอกชื่อ-นามสกุลลูกค้า" name="customername" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">สาขา <span class="file-required">*</span></label>
                            <select class="form-select" name="branchno" id="selectbranch" required>
                                <option value="" selected>กรุณาเลือกสาขา</option>
                                <?php
                                $sql = "select branchcode as cmpcode, branchname as cmpname from branch where etax_group = '$branchgroup' order by branchcode";
                                $query = pg_query($connection, $sql);
                                if (!$query) { echo "An error.\n $sql"; exit; }
                                while ($row = pg_fetch_object($query)) {
                                    echo "<option value='$row->cmpcode'>$row->cmpcode — $row->cmpname</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12"><p class="form-label mb-1">เอกสารแนบ</p></div>
                        <?php
                        $files = [
                            ['id'=>1,'label'=>'ใบกำกับภาษีค่ารถ','required'=>true],
                            ['id'=>2,'label'=>'Commission','required'=>false],
                            ['id'=>3,'label'=>'เอกสารรับเงินดาวน์','required'=>false],
                            ['id'=>4,'label'=>'ซับดาวน์','required'=>false],
                            ['id'=>5,'label'=>'ซับงวด','required'=>false],
                        ];
                        foreach ($files as $f): ?>
                        <div class="col-md-6" id="file<?php echo $f['id']; ?>">
                            <div class="file-upload-area">
                                <label class="form-label" for="picture<?php echo $f['id']; ?>">
                                    <?php echo $f['id'].'. '.$f['label']; ?>
                                    <?php if ($f['required']): ?><span class="file-required"> *</span><?php endif; ?>
                                </label>
                                <input type="file" class="form-control" id="picture<?php echo $f['id']; ?>"
                                       name="picture<?php echo $f['id']; ?>"
                                       onchange="chk_sizefile(<?php echo $f['id']; ?>)"
                                       <?php if ($f['required']) echo 'required'; ?>>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn-purple btn" onclick="confirm_submit()">💾 บันทึก</button>
                        <a href="view.php" class="btn-outline-purple btn">ดูรายการ</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (isset($_REQUEST["message"])): ?>
<div class="modal fade" id="resultModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <?php if ($_REQUEST["message"] == 'success'): ?>
            <div class="modal-body text-center py-4">
                <div style="font-size:3rem;color:var(--purple-500);">✅</div>
                <h5 class="mt-2 fw-bold" style="color:var(--purple-700);">บันทึกสำเร็จ</h5>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn-outline-purple btn" data-bs-dismiss="modal">เพิ่มรายการอีก</button>
                <a href="view.php"><button type="button" class="btn-purple btn">ดูรายการ</button></a>
            </div>
            <?php else: ?>
            <div class="modal-body text-center py-4">
                <div style="font-size:3rem;">❌</div>
                <h5 class="mt-2 fw-bold text-danger">ไม่สามารถบันทึกรายการได้</h5>
            </div>
            <div class="modal-footer border-0 justify-content-center gap-2">
                <button type="button" class="btn-outline-purple btn" data-bs-dismiss="modal">ลองอีกครั้ง</button>
                <a href="view.php"><button type="button" class="btn-purple btn">ดูรายการ</button></a>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php endif; ?>
<!-- modal for notice -->
<div class="modal fade" id="noticeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-header border-0 pb-0 justify-content-end">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center pt-0 pb-4 px-4">
                <div class="notice-icon mb-3">!</div>
                <h5 class="fw-bold mb-1 notice-title" id="noticeModalTitle"></h5>
                <p class="mb-3 notice-subtitle" id="noticeModalSubtitle"></p>
                <div class="notice-info-box text-center d-none" id="noticeModalBody"></div>
            </div>
            <div class="modal-footer border-0 pt-0 px-4 pb-4">
                <button type="button" class="btn-notice-close btn w-100" data-bs-dismiss="modal">ปิด</button>
            </div>
        </div>
    </div>
</div>

<script>
var lastmachineCheckTimer = null;
var lastmachineLastAlerted = '';
var noticeModal = null;

function showNoticeModal(message, title, infoHtml) {
    if (!noticeModal) {
        noticeModal = new bootstrap.Modal(document.getElementById('noticeModal'));
    }

    $("#noticeModalTitle").text(title || 'ไม่สามารถบันทึกข้อมูลได้');
    $("#noticeModalSubtitle").text(message);

    var $body = $("#noticeModalBody");
    if (infoHtml) {
        $body.html(infoHtml).removeClass('d-none');
    } else {
        $body.empty().addClass('d-none');
    }
    noticeModal.show();
}

function chk_sizefile(id) {
    var file = document.getElementById("picture" + id).files[0];
    if (file && file.size >= 20971520) {
        showNoticeModal("ไฟล์ที่อัพโหลดเกินขนาด (สูงสุด 20 MB)");
        var c = document.getElementById("file" + id);
        c.innerHTML = c.innerHTML;
    }
}

function checkLastMachineNo(forceCheck) {
    var machineNo = $("#lastmachine_no").val().trim();
    var shouldForceCheck = forceCheck === true;

    if (!/^\d{6}$/.test(machineNo)) {
        return $.Deferred().resolve({ exists: false }).promise();
    }

    if (!shouldForceCheck && machineNo === lastmachineLastAlerted) {
        return $.Deferred().resolve({ exists: false }).promise();
    }

    return $.ajax({
        type: "POST",
        url: "check_lastmachine.php",
        dataType: "json",
        data: { lastmachine_no: machineNo },
        success: function (response) {
            if (response && response.exists) {
                lastmachineLastAlerted = machineNo;
                showNoticeModal(
                    "เนื่องจากเลขถังนี้มีอยู่ในระบบแล้ว",
                    "ไม่สามารถบันทึกข้อมูลได้",
                    'หากต้องการบันทึกเลขถังนี้อีกครั้ง กรุณาติดต่อฝ่ายบัญชีสมใจ <br>' +
                    'ผ่าน <b style="color:#2563eb;">E-mail: dl-ap-invoice@cjk-cr.com</b> หรือ <b style="color:#45d44c;">Group LINE</b> ของดีลเลอร์ <br>' +
                    'เพื่อให้เจ้าหน้าที่ตรวจสอบและดำเนินการ'
                );
            }
        }
    });
}

function confirm_submit() {
    if (!$("#lastmachine_no").val() || !$("#selectbranch").val() || !$("#customername").val()) {

        showNoticeModal('กรุณากรอกข้อมูลให้ครบ');
        return;
    }

    checkLastMachineNo(true).done(function (response) {
        if (response && response.exists) {
            return;
        }

        if (confirm("ยืนยันการเพิ่มข้อมูล?")) {
            document.getElementById('loadingOverlay').style.display = 'flex';
            document.forms["submititem"].submit();
        }
    });
}
$(document).ready(function () {
    document.getElementById('noticeModal').addEventListener('hidden.bs.modal', function () {
        var lastmachineInput = document.getElementById('lastmachine_no');
        if (lastmachineInput) {
            lastmachineInput.focus();
            lastmachineInput.select();
        }
    });

    $("#lastmachine_no").on("input", function () {
        this.value = this.value.replace(/\D/g, '').slice(0, 6);
        lastmachineLastAlerted = '';

        clearTimeout(lastmachineCheckTimer);
        lastmachineCheckTimer = setTimeout(checkLastMachineNo, 350);
    });
    $("#selectbranch").on("change", function () {
        var m = $("#lastmachine_no").val(), b = $("#selectbranch").val();
        if (m && b) {
            $.ajax({ type:"POST", url:'checkduplicat.php', data:{lastmachine_no:m, branchno:b},
                success: function(r) { var d=JSON.parse(r); if(d.ic_no) confirm("รายการนี้ถูกรับในเอกสาร : "+d+" แล้ว ต้องการส่งอีกหรือไม่"); }
            });
        }
    });
    <?php if (isset($_REQUEST["message"])): ?>
    new bootstrap.Modal(document.getElementById('resultModal')).show();
    <?php endif; ?>
});
</script>
<?php include("./config/ssDBclose.php") ?>
</body>
</html>
