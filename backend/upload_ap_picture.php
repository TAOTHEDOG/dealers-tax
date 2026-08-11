<?php
include("./../config/config.php");
session_start();

if (!isset($_SESSION) || (($_SESSION['role'] != "Accountant") && ($_SESSION['role'] != "Admin") && ($_SESSION['role'] != "CS"))) {
    echo "<script>location.href='" . $hostname . "/login.php'</script>";
    return false;
}

$result = pg_query($connections,
    "SELECT p.id, p.ap_no, p.filename, p.original_name,
            to_char(p.created_at , 'YYYY-MM-DD HH24:MI') AS created_at,
            u.dealer AS created_by_name
     FROM ap_pictures p
     LEFT JOIN users u ON u.id = p.created_by
     ORDER BY p.created_at DESC"
);

$pictures = [];
while ($row = pg_fetch_assoc($result)) {
    $pictures[] = $row;
}

include("../config/dbcloseconnect.php");
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Tax — อัปโหลดเอกสาร</title>
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="./../style/style.css?v=3" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<?php if (!empty($_REQUEST['message'])): ?>
<script>
    Swal.fire({
        icon: '<?php echo (isset($_REQUEST['status']) && $_REQUEST['status'] == 'error') ? 'error' : 'success'; ?>',
        title: <?php echo json_encode($_REQUEST['message']); ?>,
        showConfirmButton: false,
        timer: 3000
    });
</script>
<?php endif; ?>

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
            <div class="ms-auto">
                <button class="btn-purple btn btn-sm" data-bs-toggle="modal" data-bs-target="#UploadModal">
                    + อัปโหลดรูปภาพ
                </button>
            </div>
        </div>

        <div class="content-area">
            <div class="table-container">
                <div class="table-responsive">
                    <table id="pictureTable" class="display w-100">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>เลขที่เอกสาร</th>
                                <th>ชื่อไฟล์</th>
                                <th>อัปโหลดโดย</th>
                                <th>วันที่อัปโหลด</th>
                                <th>ไฟล์</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pictures as $i => $pic): ?>
                            <tr>
                                <td><?php echo $i + 1; ?></td>
                                <td><?php echo htmlspecialchars($pic['ap_no']); ?></td>
                                <td><?php echo htmlspecialchars($pic['original_name']); ?></td>
                                <td><?php echo htmlspecialchars($pic['created_by_name'] ?? '-'); ?></td>
                                <td><?php echo htmlspecialchars($pic['created_at']); ?></td>
                                <td>
                                    <a href="<?php echo $hostname; ?>/docs/<?php echo urlencode($pic['filename']); ?>"
                                       target="_blank" class="btn-file btn btn-sm">⬇ ดาวน์โหลด</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Upload Modal -->
<div class="modal fade" id="UploadModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header" style="background:var(--purple-700); color:#fff;">
                <h5 class="modal-title fw-bold">อัปโหลดเอกสาร</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="save_ap_picture.php" enctype="multipart/form-data"
                  class="needs-validation" novalidate>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">เลขที่เอกสาร<span class="file-required">*</span></label>
                        <input type="text" class="form-control" name="ap_no"
                               placeholder="ใส่เลขที่เอกสาร" required>
                        <div class="invalid-feedback">กรุณาใส่เลขที่เอกสาร</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">รูปภาพ / เอกสาร <span class="file-required">*</span></label>
                        <div class="file-upload-area">
                            <input type="file" class="form-control" name="picture[]"
                                   accept="image/*,.pdf" multiple required>
                        </div>
                        <div class="invalid-feedback">กรุณาเลือกไฟล์</div>
                        <small class="text-muted">รองรับ JPG, PNG, PDF ขนาดไม่เกิน 20 MB ต่อไฟล์ (เลือกหลายไฟล์ได้)</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn-outline-purple btn" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn-purple btn">อัปโหลด</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function () {
    $("#pictureTable").DataTable({
        order: [[4, 'desc']],
        pageLength: 25,
        language: {
            search: 'ค้นหา:',
            lengthMenu: 'แสดง _MENU_ รายการ',
            info: 'แสดง _START_–_END_ จาก _TOTAL_ รายการ',
            infoEmpty: 'ไม่มีข้อมูล',
            infoFiltered: '(กรองจาก _MAX_ รายการทั้งหมด)',
            paginate: { first: '«', last: '»', next: '›', previous: '‹' },
            zeroRecords: 'ไม่พบข้อมูลที่ตรงกัน'
        }
    });
});

(function () {
    'use strict';
    document.querySelectorAll('.needs-validation').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!form.checkValidity()) { e.preventDefault(); e.stopPropagation(); }
            form.classList.add('was-validated');
        }, false);
    });
})();
</script>
</body>
</html>
