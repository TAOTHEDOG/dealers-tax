<?php
include("./../config/config.php");
include("./../config/ssDB.php");

// if (isset($_SESSION['username'])) {
//     // session_destroy();
// }  else {
//     header( "location: login.php" );
//     exit(0);
// }
session_start();
if ((!isset($_SESSION)) || (($_SESSION['role'] != "Accountant") && ($_SESSION['role'] != "Admin"))) {

  echo ("<script>location.href='" . $hostname . "/login.php'</script>");
  // header("location: login.php");
  // exit(0);
  return false;
}

// $branchgroup = $_SESSION['branchgroup'];

// $sql = "select cmpcode,cmpname from tblcmpcode where branchgroup = '{$branchgroup}'";
// $query = pg_query($connections, $sql);
// if (!$query) {
//     echo "An error.\n $sql";
//     exit;
// }
// $branchno = '';
// while ($row = pg_fetch_object($query)) {
//     $branchno = $branchno . "$row->cmpcode" . ',';
// }
// $branchno = substr_replace($branchno, "", -1);

?>
<!DOCTYPE html>

<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Account Tax</title>
  <!-- <link rel="stylesheet" type="text/css" href="../assets/css/bootstrap.min.css"> -->
  <link rel="icon" type="image/x-icon" href="./assets/images/favicon.ico">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
  <link href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css" rel="stylesheet">
  <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" />
  <link href="./../style/style.css" rel="stylesheet">

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
  <!-- <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
  <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script> -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
  <?php
  if (!empty($_REQUEST['message'])) {
  ?>
    <script type="text/javascript">
      Swal.fire({
        // position: 'top-end',
        icon: 'success',
        title: <?php echo json_encode($_REQUEST['message']); ?>,
        showConfirmButton: false,
        timer: 3000
      }).then(function() {
        // var hostname = 'http://localhost/dealertax/backend/index.php';
        window.location.href = <?php echo json_encode($hostname) ?> + '/backend/index.php';
      })
    </script>

  <?php } ?>
  <div class="container-fluid">
    <div class="row flex-nowrap">

      <div class="col-auto px-0">
        <?php include("sidebar_account.php") ?>
      </div>

      <div class="col py-3">
        <div class="navbar d-flex">
          <a href="" data-bs-target="#sidebar" data-bs-toggle="collapse" class="p-1 text-decoration-none">
            <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" fill="currentColor" class="bi bi-justify" viewBox="0 0 16 16">
              <path fill-rule="evenodd" d="M2 12.5a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5zm0-3a.5.5 0 0 1 .5-.5h11a.5.5 0 0 1 0 1h-11a.5.5 0 0 1-.5-.5z" />
            </svg>
          </a>
          <div class="center_div">
            <h4><?php echo $_SESSION['dealer']; ?></h4> <i class="bi bi-justify"></i>
          </div>
          <div></div>
        </div>
        <br>

        <?php

        include("../config/config.php");

        $query = "SELECT items.*, users.dealer, users.name, users.branchgroup FROM items LEFT JOIN users ON items.dealer_id = users.id WHERE items.ischeck = FALSE and items.status <> '7' ORDER BY items.created_at DESC";
        // echo "query";
        // echo "<pre>";
        // print_r($query);
        $result = pg_query($connections, $query);

        // $rowcount=mysqli_num_rows($result);
        $rowcount = pg_num_rows($result);

        $query1 = "SELECT lastmachine_no FROM items WHERE (status = '1' OR status = '2')";
        $result1 = pg_query($connections, $query1);
        $item_waitmanage = pg_num_rows($result1);
        ?>

        <?php include("../config/dbcloseconnect.php") ?>
        <div class="row" style="padding-left: 10px; padding-right: 19px; width: 100%;">

          <div class="d-flex">
            <div class="row justify-content-start col-lg-10" style="padding-bottom: 10px;">
              <div class="card col-md-4 col-sm-3">
                <div class="card-body">
                  <h5 class="card-title">จำนวนทั้งหมด <span style="color: red;"><?php echo $rowcount; ?></span></h5>
                  <p class="card-text"></p>
                </div>
              </div>
              <div class="card col-md-4 col-sm-3">
                <div class="card-body">
                  <h5 class="card-title">จำนวนที่ยังไม่เช็ค <span style="color: red;"><?php echo $item_waitmanage; ?></span></h5>
                  <p class="card-text"></p>
                </div>
              </div>
            </div>
            <div class="d-flex justify-content-end col-lg-2"><button style="background-color: #79AC78;" class="btn" onclick="updateData()">Update</button></div>
          </div>


          <hr style="width: 99.5%;">
          <table class="table align-middle mb-0 bg-white table-hover" id="myTable" style="width:100%">
            <thead class="bg-light">
              <tr>
                <th>dealer</th>
                <th>สาขา</th>
                <th>วันที่-เวลา</th>
                <th>เลขถัง 6 หลัก</th>
                <!-- <th>เลขถังเต็มจำนวน</th> -->
                <th>ชื่อลูกค้า</th>
                <th style="text-align: center;">จัดการข้อมูล</th>
                <th>สถานะ</th>
                <th>วันที่จ่าย</th>
              </tr>
            </thead>
            <tbody>

              <?php while ($row = pg_fetch_object($result)) { ?>
                <tr>
                  <td>
                    <div class="d-flex align-items-center">
                      <?php echo $row->dealer; ?>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <?php echo $row->branchgroup; ?>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <?php
                      $dt = new DateTime($row->created_at);

                      $date = $dt->format('Y-m-d');
                      $time = $dt->format('H:i:s');

                      $ex_date = explode('-', $date);

                      // $year = intval($ex_date[0]) + 543;
                      $year = $ex_date[0];

                      // $display_date = $year . "-" . $ex_date[1] . "-" . $ex_date[2] . " " . $time;
                      $display_date = $year + 543 . "-" . $ex_date[1] . "-" . $ex_date[2];
                      echo $display_date;
                      ?>
                    </div>
                  </td>
                  <td>
                    <div class="d-flex align-items-center">
                      <?php echo $row->lastmachine_no; ?>
                    </div>
                  </td>
                  <!-- <td>
                    <div class="d-flex align-items-center">
                      <?php //echo $row->machine_no; 
                      ?>
                    </div>
                  </td> -->
                  <td>
                    <div class="d-flex align-items-center">
                      <?php echo $row->customername; ?>
                    </div>
                  </td>
                  <td>
                    <div style="text-align: start;">

                      <?php
                      if ($row->status == '1' || $row->status == '2') {
                      ?>
                        <a href="#" class="btn btn-link btn-sm btn-rounded" style="background-color: #79AC78; max-width: 110px;" onclick="show_data_file(<?php echo $row->id ?>)">
                          Check
                        </a>&nbsp;

                        <a href="#" class="btn btn-link btn-sm btn-rounded" style="background-color: red; max-width: 110px;" onclick="confirm_comment(<?php echo $row->id ?>)">Reject</a>
                      <?php } elseif ($row->status == 3) { ?>
                        <?php if ($_SESSION['role'] == "Admin") { ?>
                          <a href="#" class="btn btn-sm btn-secondary" onclick="edit_machineno(<?php echo $row->id ?>)" style="max-width: 110px; background-color: #FFB000;">แก้เลขถัง</a>&nbsp;
                        <?php } ?>
                        <a href="#" class="btn btn-link btn-sm btn-rounded" style="max-width: 220px;">
                          รอ Paid
                        </a>
                      <?php
                      } elseif ($row->status == '6') {
                      ?>
                        <a href="#" class="btn btn-link btn-sm btn-rounded" style="max-width: 220px;background-color: #FF6969;">
                          Rejected
                        </a>
                      <?php
                      } elseif ($row->status == '4') {
                      ?>
                        <a href="#" class="btn btn-link btn-sm btn-rounded" style="max-width: 220px;">
                          แก้ไข machineno รอ Paid
                        </a>
                      <?php
                      }
                      ?>
                    </div>
                  </td>
                  <!--   <td>    
                        <div class="d-flex align-items-center" style="padding-bottom: 10px;">    
                          <?php
                          $disabled = "disabled";
                          if ($row->status != 5 && $row->status != 6) {
                            $disabled = "";
                          }
                          ?>   
                           
                        </div>   
                      </td> -->
                  <td>
                    <div class="d-flex align-items-center">
                      <?php

                      if ($row->status == '1') {
                        echo "Pending";
                      } elseif ($row->status == '2') {
                        echo "Processing";
                      } elseif ($row->status == '3') {
                        echo "Waiting";
                      } elseif ($row->status == '4') {
                        echo "Edit machineno";
                      } elseif ($row->status == '5') {
                        echo "Paid";
                      } elseif ($row->status == '6') {
                        echo "Reject";
                      }
                      ?>
                    </div>
                  </td>
                  <?php
                  if ($row->status == '5') {
                  ?>
                    <td>
                      <div class="d-flex align-items-center">
                        <?php echo $row->vat_date; ?>
                      </div>
                    </td>
                  <?php
                  } else {
                  ?>
                    <td>
                      <div class="d-flex align-items-center">
                      </div>
                    </td>
                  <?php
                  } ?>



                </tr>
              <?php } ?>

            </tbody>
          </table>
        </div>

      </div>

      <!-- Modal -->
      <div class="modal fade" id="ResultModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="exampleModalLabel" style="font-weight: bold;">Processing</h4>
              <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
            </div>
            <div class="modal-body">
              <h6 style="font-weight: bold; padding-left: 5px;">รายการเอกสาร</h6>
              <div class="mb-3" id="data_item">

              </div>
              <hr>
              <h6 style="font-weight: bold; padding-left: 5px;">ข้อมูลจาก shiftsoft</h6>
              <div class="mb-3" id="data_shiftsoft">

              </div>
            </div>
            <div class="modal-footer">
              <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>&nbsp;
              <button type="button" class="btn btn-primary" style="background-color: #0d6efd;display: none;" onclick="confirm_submit();" id="submit_select">ยืนยัน</button>
            </div>
          </div>
        </div>
      </div>

      <div class="modal fade" id="CommentModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="exampleModalLabel" style="font-weight: bold;">Reject Item</h4>
              <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
            </div>
            <form class="needs-validation" novalidate method="post" action="reject_item.php" id="reject_item" onsubmit=" var machineno = document.getElementById('reject_machineno').value; return confirm('ยืนยันการ Reject รายการเลขถัง '+ machineno + ' ?');">
              <div class="modal-body">
                <div class="row">
                  <h6 style="font-weight: bold; padding-left: 5px;">รายการเอกสาร</h6>
                  <div class="mb-3" id="data_reject_item">

                  </div>

                </div>
                <div class="row">
                  <div class="mb-3">
                    <label for="reject_machineno" class="col-form-label">รายการเลขถัง:</label>
                    <input type="text" class="form-control" id="reject_machineno" name="reject_machineno" readonly="readonly">
                  </div>
                  <div class="mb-3">
                    <label for="comment" class="col-form-label">ข้อความตอบกลับ:</label>
                    <textarea class="form-control needs-validation" id="comment" name="comment" placeholder="ข้อความตอบกลับแจ้ง dealer" required></textarea>
                    <div class="invalid-feedback">
                      กรุณาพิมพ์ข้อความตอบกลับ.
                    </div>
                  </div>
                  <input type="hidden" class="form-control" id="item_id" name="item_id">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>&nbsp;
                <button type="submit" class="btn btn-primary" style="background-color: #cc2f00;" id="btn_reject">ยืนยัน</button>
              </div>
            </form>
          </div>
        </div>
      </div>

      <div class="modal fade" id="EditModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content">
            <div class="modal-header">
              <h4 class="modal-title" id="exampleModalLabel" style="font-weight: bold;">แก้ไขเลขถัง</h4>
              <!-- <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button> -->
            </div>
            <form class="needs-validation" novalidate method="post" action="editmachineno.php" id="editmachineno" onsubmit=" var machineno = document.getElementById('edit_machineno').value; return confirm('ยืนยันการแก้ไขเลขถัง รายการเลขถัง '+ machineno + ' ?');">
              <div class="modal-body">
                <div class="row">
                  <h6 style="font-weight: bold; padding-left: 5px;">รายการเอกสาร</h6>
                  <div class="mb-3" id="data_edit_item">

                  </div>

                </div>
                <div class="row">
                  <div class="mb-3">
                    <label for="edit_machineno" class="col-form-label">รายการเลขถัง:</label>
                    <input type="text" class="form-control" id="edit_machineno" name="edit_machineno" readonly="readonly">
                  </div>
                  <div class="mb-3">
                    <label for="correct_machine" class="col-form-label">เลขถังที่ถูกต้อง:</label>
                    <input class="form-control needs-validation" id="correct_machine" name="correct_machine" placeholder="ใส่เลขถังที่ถูกต้อง" minlength="17" maxlength="17" required>
                    <div class="invalid-feedback">
                      กรุณาใส่เลขถังที่ถูกต้อง
                    </div>
                  </div>
                  <input type="hidden" class="form-control" id="edit_item_id" name="edit_item_id">
                </div>
              </div>
              <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>&nbsp;
                <button type="submit" class="btn btn-primary" style="background-color: #0d6efd;" id="btn_edit">ยืนยัน</button>
              </div>
            </form>
          </div>
        </div>
      </div>

    </div>
  </div>

  <script>
    $(document).ready(function() {
      $("#myTable").DataTable({
        "responsive": true,
        "columns": [
          null,
          {
            "width": "50px"
          },
          {
            "width": "100px"
          },
          {
            "width": "50px"
          },
          null,
          null,
          {
            "width": "80px"
          },
          null
        ],
        dom: 'Bfrtip',
        buttons: [
          'excel', 'print'
        ]
      });
    });
  </script>

  <script type="text/javascript">
    function show_data_file(id) {
      // body...
      // alert(id)
      $.ajax({
        type: "POST",
        url: 'start_process.php',
        data: {
          item_id: id
        },
        success: function(response) {
          var jsonData = JSON.parse(response);
          console.log(jsonData['item']);

          $('#ResultModal').modal('show');
          $("#data_item").empty();
          $("#data_shiftsoft").empty();

          // Download file;
          itemAdd = "";
          itemAdd += '<div id="row">';
          itemAdd += '<ul class="list-unstyled">';
          if (jsonData['item']['doc1'] != "" && jsonData['item']['doc1'] != null) {
            itemAdd += '<li>1. ใบกำกับภาษีค่ารถ  <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc1'] + '" download="' + jsonData['item']['doc1'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc2'] != "" && jsonData['item']['doc2'] != null) {
            itemAdd += '<li>2. Commission <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc2'] + '" download="' + jsonData['item']['doc2'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc3'] != "" && jsonData['item']['doc3'] != null) {
            itemAdd += '<li>3. เอกสารรับเงินดาวน์ <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc3'] + '" download="' + jsonData['item']['doc3'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc4'] != "" && jsonData['item']['doc4'] != null) {
            itemAdd += '<li>4. ซับดาวน์ <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc4'] + '" download="' + jsonData['item']['doc4'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc5'] != "" && jsonData['item']['doc5'] != null) {
            itemAdd += '<li>5. ซับงวด <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc5'] + '" download="' + jsonData['item']['doc5'] + '">ดาวน์โหลด</a> </li>';
          }

          itemAdd += '</ul>';
          itemAdd += '</div>';

          $('#data_item').append(itemAdd);

          if (jsonData['machine_datas'] != "" && jsonData['machine_datas'].length > 0) {

            var machine_datas = jsonData['machine_datas'];

            var rowcount = machine_datas.length;
            form_submit = "";
            form_submit += '<form action="select_item.php" method="POST" id="select_form">' +
              '<div class="row">' +
              '<div class="col-md-2 text-center"> <label class="col-form-label"> วันที่ </label> ' +
              '</div>' +
              '<div class="col-md-3 text-center"> <label class="col-form-label"> เลขที่ใบรับ </label> ' +
              '</div>' +
              '<div class="col-md-4 text-center"> <label class="col-form-label"> เลขถัง </label> ' +
              '</div>' +
              '<div class="col-md-3 text-center"> <label class="col-form-label"> เลือกรายการ </label> ' +
              '</div>' +
              '</div>';
            // $('#data_shiftsoft').append(newheader);

            for (var i = 0; i < rowcount; i++) {

              let year = machine_datas[i].icdate.substring(0, 4);
              let month = machine_datas[i].icdate.substring(4, 6);
              let day = machine_datas[i].icdate.substring(6, 8);
              console.log(year);
              console.log(month);
              console.log(day);

              let icdate = year + "-" + month + "-" + day;
              let apdate = "";
              // let icdate = new Date(date).format('YYYY-MM-D');
              if (machine_datas[i].apdate != null && machine_datas[i].apdate != "") {
                let apyear = machine_datas[i].apdate.substring(0, 4);
                let apmonth = machine_datas[i].apdate.substring(4, 6);
                let apday = machine_datas[i].apdate.substring(6, 8);

                apdate = apyear + "-" + apmonth + "-" + apday;
              }

              newRowAdd =

                '<div class="row">' +
                '<div class="col-md-2">' +
                '<input type="text" class="form-control" placeholder="วันที่" id="icdate_' + i + '" value="' + icdate + '" disabled readonly>' +
                '</div>' +

                '<div class="col-md-3">' +
                '<input type="text" class="form-control" placeholder="ใบกำกับภาษี" id="icno_' + i + '" value="' + machine_datas[i].icno + '" disabled readonly>' +
                '</div>' +

                '<div class="col-md-4">' +
                '<input type="text" class="form-control" placeholder="เลขถัง" id="machineno_' + i + '" value="' + machine_datas[i].chassisno + '" disabled readonly>' +
                '</div>' +

                '<div class="col-md-3 text-center" style="padding-top: 10px; background-color: #e9ecef;">' +
                '<input type="checkbox" class="form-check-input" name="select_item" id="item_' + i + '" value="' + machine_datas[i].chassisno + '" aria-label="Checkbox for following text input" onchange="select_machine(' + i + ');">' +
                '</div>' +

                '<input type="hidden" class="form-control" id="apno_' + i + '" value="' + machine_datas[i].apno + '">' +
                '<input type="hidden" class="form-control" id="apdate_' + i + '" value="' + apdate + '">' +
                '</div>';

              // $('#data_shiftsoft').append(newRowAdd);
              form_submit += newRowAdd;
            }
            // shift soft data
            form_submit += '<input type="hidden" id="select_icdate" name="select_icdate" value="" >';
            form_submit += '<input type="hidden" id="select_icno" name="select_icno" value="" >';
            form_submit += '<input type="hidden" id="select_machineno" name="select_machineno" value="" >';
            form_submit += '<input type="hidden" id="select_apno" name="select_apno" value="" >';
            form_submit += '<input type="hidden" id="select_apdate" name="select_apdate" value="" >';

            form_submit += '<input type="hidden" id="item_id" name="item_id" value="' + jsonData['item']['id'] + '" >';
            form_submit += '<input type="hidden" id="lastmachine_no" name="lastmachine_no" value="' + jsonData['item']['lastmachine_no'] + '" >';
            // $('#data_shiftsoft').append(hdfield);

            form_submit += '</form>';
            $('#data_shiftsoft').append(form_submit);

            document.getElementById('submit_select').style.display = "block";
            // }
          } else {
            // alert("ยังไม่มีข้อมูลจาก shiftsoft");
          }

          // location.reload();
        }
      });
    };
  </script>

  <script type="text/javascript">
    function select_machine(i) {

      if (document.getElementById('item_' + i).checked) {
        var machineno = document.getElementById('machineno_' + i).value;
        // console.log(value);
        document.getElementById('select_machineno').value = machineno;

        var icdate = document.getElementById('icdate_' + i).value;

        document.getElementById('select_icdate').value = icdate;

        var icno = document.getElementById('icno_' + i).value;

        document.getElementById('select_icno').value = icno;

        var apdate = document.getElementById('apdate_' + i).value;

        document.getElementById('select_apdate').value = apdate;

        var apno = document.getElementById('apno_' + i).value;

        document.getElementById('select_apno').value = apno;
      } else {
        document.getElementById('select_machineno').value = null;
        document.getElementById('select_icdate').value = null;
        document.getElementById('select_icno').value = null;
        document.getElementById('select_apdate').value = null;
        document.getElementById('select_apno').value = null;
      }
    }

    function confirm_submit() {
      Swal.fire({
        title: 'ยืนยันการทำรายการ?',
        text: "You want to confirm this item!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'ยืนยัน!',
        cancelButtonText: "ปิด",
      }).then((result) => {
        if (result.isConfirmed) {
          $('#select_form').submit();
        }
      })
    }

    function confirm_comment(item_id) {

      $.ajax({
        type: "POST",
        url: 'confirm_comment.php',
        data: {
          item_id: item_id
        },
        success: function(response) {
          var jsonData = JSON.parse(response);
          console.log("data item");
          console.log(jsonData['item']);
          console.log(jsonData['machine_datas']);

          $("#data_reject_item").empty();
          // $("#data_shiftsoft").empty();

          $("#CommentModal").modal('show');

          // Download file;
          itemAdd = "";
          itemAdd += '<div id="row">';
          itemAdd += '<ul class="list-unstyled">';
          if (jsonData['item']['doc1'] != "" && jsonData['item']['doc1'] != null) {
            itemAdd += '<li>1. ใบกำกับภาษีค่ารถ  <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc1'] + '" download="' + jsonData['item']['doc1'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc2'] != "" && jsonData['item']['doc2'] != null) {
            itemAdd += '<li>2. Commission <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc2'] + '" download="' + jsonData['item']['doc2'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc3'] != "" && jsonData['item']['doc3'] != null) {
            itemAdd += '<li>3. เอกสารรับเงินดาวน์ <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc3'] + '" download="' + jsonData['item']['doc3'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc4'] != "" && jsonData['item']['doc4'] != null) {
            itemAdd += '<li>4. ซับดาวน์ <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc4'] + '" download="' + jsonData['item']['doc4'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc5'] != "" && jsonData['item']['doc5'] != null) {
            itemAdd += '<li>5. ซับงวด <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc5'] + '" download="' + jsonData['item']['doc5'] + '">ดาวน์โหลด</a> </li>';
          }

          itemAdd += '</ul>';
          itemAdd += '</div>';

          $('#data_reject_item').append(itemAdd);

          $('#reject_machineno').val(jsonData['item']['lastmachine_no']);

          $('#item_id').val(jsonData['item']['id']);

        }
      });

    }

    function edit_machineno(item_id) {

      $.ajax({
        type: "POST",
        url: 'edit_machineno.php',
        data: {
          item_id: item_id
        },
        success: function(response) {
          var jsonData = JSON.parse(response);

          $("#data_edit_item").empty();
          // $("#data_shiftsoft").empty();
          $("#EditModal").modal('show');

          // Download file;
          itemAdd = "";
          itemAdd += '<div id="row">';
          itemAdd += '<ul class="list-unstyled">';
          if (jsonData['item']['doc1'] != "" && jsonData['item']['doc1'] != null) {
            itemAdd += '<li>1. ใบกำกับภาษีค่ารถ  <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc1'] + '" download="' + jsonData['item']['doc1'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc2'] != "" && jsonData['item']['doc2'] != null) {
            itemAdd += '<li>2. Commission <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc2'] + '" download="' + jsonData['item']['doc2'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc3'] != "" && jsonData['item']['doc3'] != null) {
            itemAdd += '<li>3. เอกสารรับเงินดาวน์ <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc3'] + '" download="' + jsonData['item']['doc3'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc4'] != "" && jsonData['item']['doc4'] != null) {
            itemAdd += '<li>4. ซับดาวน์ <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc4'] + '" download="' + jsonData['item']['doc4'] + '">ดาวน์โหลด</a> </li>';
          }

          if (jsonData['item']['doc5'] != "" && jsonData['item']['doc5'] != null) {
            itemAdd += '<li>5. ซับงวด <a href="' + <?php echo json_encode($hostname) ?> + '/docs/' + jsonData['item']['doc5'] + '" download="' + jsonData['item']['doc5'] + '">ดาวน์โหลด</a> </li>';
          }

          itemAdd += '</ul>';
          itemAdd += '</div>';

          $('#data_edit_item').append(itemAdd);

          $('#edit_machineno').val(jsonData['item']['lastmachine_no']);

          $('#edit_item_id').val(jsonData['item']['id']);

        }
      });

    }
  </script>
  <script>
    // Example starter JavaScript for disabling form submissions if there are invalid fields
    (function() {
      'use strict';
      window.addEventListener('load', function() {
        // Fetch all the forms we want to apply custom Bootstrap validation styles to
        var forms = document.getElementsByClassName('needs-validation');
        // Loop over them and prevent submission
        var validation = Array.prototype.filter.call(forms, function(form) {
          form.addEventListener('submit', function(event) {
            if (form.checkValidity() === false) {
              event.preventDefault();
              event.stopPropagation();
            }
            form.classList.add('was-validated');
          }, false);
        });
      }, false);
    })();

    // var el = document.getElementById('reject_item');

    // el.addEventListener('submit', function(){
    //   var machineno = document.getElementById("reject_machineno").value;
    //   // return confirm('ยืนยันการ Reject รายการเลขถัง' + machineno + '?');

    // }, false);
  </script>
  <script>
    function updateData() {
      location.href = "./updateauto.php";
    }
  </script>

  <!-- As a heading -->

  <script type="text/javascript" src="../assets/js/bootstrap.min.js"></script>
</body>

</html>