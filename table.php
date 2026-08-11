<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>SJ Dealer App</title>
    <link rel="icon" type="image/x-icon" href="./assets/images/favicon.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
    <link href="./style/style.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

</head>

<body>
    <div class="dashboard_group">
        <div class="row groupcard">
            <div class="card col-md-2">
                <div class="card-body">
                    <h5 class="card-title">จำนวนทั้งหมด</h5>
                    <p class="card-text">100</p>
                </div>
            </div>
            <div class="card col-md-2">
                <div class="card-body">
                    <h5 class="card-title">จำนวนที่ยังไม่เช็ค</h5>
                    <p class="card-text">20</p>
                </div>
            </div>
        </div>
        <div class="frm_table">
            <table id="myTable" class="display" style="width: 100%;">
                <thead>
                    <tr>
                        <th>สาขา</th>
                        <th>วันที่บันทึก</th>
                        <th>เลขถัง</th>
                        <th>ชื่อ-นามสกุลลูกค้า</th>
                        <th>ไฟล์แนบ</th>
                        <th>สถานะ</th>
                        <th>เลขใบกำกับภาษี</th>
                        <th>Checked</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <th>401</th>
                        <th>01/09/2566</th>
                        <th>XXXXXXXXXXXXX</th>
                        <th>XXX XXXXX XXXXX</th>
                        <th><button class="btn filebtn" data-bs-toggle="modal" data-bs-target="#fileModal">เอกสาร</button></th>
                        <th>XX</th>
                        <th>XXXXXXXXXXXXX</th>
                        <th>
                            <div class="form-check form-switch"><input class="form-check-input" type="checkbox" checked></div>
                        </th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal File-->
    <div class="modal fade" id="fileModal" tabindex="-1" aria-labelledby="fileModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="fileModalLabel">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    ...
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $("#myTable").DataTable({
                "columns": [{
                        "width": "50px"
                    },
                    {
                        "width": "100px"
                    },
                    null,
                    null,
                    {
                        "width": "100px"
                    },
                    {
                        "width": "50px"
                    },
                    null,
                    {
                        "width": "50px"
                    }
                ],
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'excel', 'pdf', 'print'
                ]
            });
        });
    </script>
</body>

</html>