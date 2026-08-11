<?php require("./config/ssDB.php"); ?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check-customers</title>
    <link rel="icon" type="image/x-icon" href="./asset/image/favicon.ico">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link href="./style/style.css" rel="stylesheet">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL" crossorigin="anonymous"></script>

    <style type="text/css">
        .header {
          padding: 4px 10px;
          /*background: #555;*/
          /*color: #f1f1f1;*/
        }

        .content {
          padding: 10px;
        }
       /* .sticky {
          position: fixed;
          top: 20%;
          width: 100%;
        }

        .sticky + .content {
          padding-top: 102px;
        }*/

        .sticky {
          position: -webkit-sticky;
          position: sticky;
          top: 0;
          /*background-color: yellow;*/
          padding: 50px;
          /*font-size: 20px;*/
        }
    </style>
</head>

<body>
    <?php

    //----------------------------LOGIN PART-----------------------------//
    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = "SELECT username,passwords from tblusername  where cancel='N' and username = '$username' AND passwords = '$password'";
    $result = pg_query($ssConnec, $query);

    $count = 0;
    if(!$result) {
        echo $query;
    }
    while ($row = pg_fetch_object($result)) {
        $_POST['username'] = $row->username;
        $_POST['password'] = $row->passwords;
        $count++;
    }
    if ($count <= 0) {
        header("Location: ./login.php");
    }
    ?>


    <div class="divindex-center flex-column">
        <form class="form-check col-md-8">
            <h1 class="h3 mb-3  fw-normal">ระบบเช็คประวัติลูกค้า</h1><span class="mb-3">*ค้นหาจากเลขบัตรประชาชน เบอร์โทรศัพท์ หรือที่อยู่</span>
            <div class="row my-3">
                <div class="mb-3 col-md-6">
                    <label>เลขบัตรประชาชน</label>
                    <input type="text" class="form-control" id="idCard">
                </div>
                <div class="mb-3 col-md-6">
                    <label>เบอร์โทรศัพท์</label>
                    <input type="text" class="form-control" id="phoneNo">
                </div>
            </div>
            <div class="row my-3">
                <div class="mb-3 col-md-6">
                    <label>ชื่อ</label>
                    <input type="text" class="form-control" id="firstname">
                </div>
                <div class="mb-3 col-md-6">
                    <label>นามสกุล</label>
                    <input type="text" class="form-control" id="lastname">
                </div>
            </div>
            <hr>
            <div class="my-3">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <div class="row">
                            <div class="col-md-4">
                                <label>บ้านเลขที่</label>
                                <input type="text" class="form-control" id="homeno">
                            </div>
                            <div class="col-md-4">
                                <label>หมู่</label>
                                <input type="text" class="form-control" id="moo">
                            </div>
                            <div class="col-md-4">
                                <label>ซอย</label>
                                <input type="text" class="form-control" id="soi">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label>ถนน</label>
                        <input type="text" class="form-control" id="tanann">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label>ตำบล</label>
                        <input type="text" class="form-control" id="tambol">
                    </div>
                    <div class="col-md-3">
                        <label>อำเภอ</label>
                        <input type="text" class="form-control" id="amphon">
                    </div>
                    <div class="col-md-3">
                        <label>จังหวัด</label>
                        <input type="text" class="form-control" id="province">
                    </div>
                    <div class="col-md-3">
                        <label>รหัสไปรษณืย์</label>
                        <input type="text" class="form-control" id="postcode">
                    </div>
                </div>
            </div>
            <div class="d-flex justify-content-center w-100">
                <button class="w-50 btn btn-primary my-3" type="submit" id="btnSearch">ค้นหา</button>
            </div>
            
        </form>
        <hr>
        <button type="button" class="btn btn-success" id="downloadExcel">EXCEL</button>
        <div class="table2">
            <table class="table table-striped" id="table">
                <thead class="header">
                    <tr>
                        <th>#</th>
                        <th>เลขที่สัญญา</th>
                        <th>วันที่ทำสัญญา</th>
                        <th>รหัสลูกค้า</th>
                        <th>ชื่อลูกค้า</th>

                        <th>ราคา</th>
                        <th>ยอดเช่าซื้อ</th>
                        <th>ชำระ</th>
                        <th>ค้าง</th>
                        <th>จำนวน</th>
                        <th>คงเหลือ</th>
                        <th>ไฟแนนซ์</th>
                        <th>ปิดบัญชี</th>
                        <th>ลักษณะการปิด</th>
                        <th>เกรดสัญญา</th>
                        <th>สถานะลูกค้า</th>
                        <th>Blicklist</th>
                    </tr>
                <tbody>

                </tbody>
                </thead>
            </table>
        </div>
    </div>


    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdn.rawgit.com/rainabba/jquery-table2excel/1.1.0/dist/jquery.table2excel.min.js"></script>
    <script>
        $(document).ready(function() {
            $("form").submit(function(event) {
                var formData = {
                    idCard: $("#idCard").val(),
                    phoneNo: $("#phoneNo").val(),
                    firstname: $("#firstname").val(),
                    lastname: $("#lastname").val(),

                    homeno: $("#homeno").val(),
                    moo: $("#moo").val(),
                    soi: $("#soi").val(),
                    tanann: $("#tanann").val(),
                    tambol: $("#tambol").val(),
                    amphon: $("#amphon").val(),
                    province: $("#province").val(),
                    postcode: $("#postcode").val(),
                };

                $.ajax({
                    type: "POST",
                    url: "query.php",
                    data: formData,
                    dataType: "json",
                    encode: true,
                }).done(function(data) {

                    let rowData = data.rowData;
                    let i = 0;

                    $("table tbody tr").remove();

                    rowData.forEach(function myFunction(item1, index1) {
                        i++;

                        let html = `<tr>
                                        <th>${i}</th>
                                        <td>${item1[1]}</td>
                                        <td>${item1[2]}</td>
                                        <td>${item1[3]}</td>
                                        <td>${item1[4]}</td>

                                        <td>${item1[5]}</td>
                                        <td>${item1[6]}</td>
                                        <td>${item1[7]}</td>
                                        <td>${item1[8]}</td>
                                        <td>${item1[9]}</td>
                                        <td>${item1[10]}</td>
                                        <td>${item1[11]}</td>
                                        <td>${item1[12]}</td>
                                        <td>${item1[13]}</td>
                                        <td>${item1[14]}</td>
                                        <td>${item1[0]}</td>
                                        <td>${item1[15]}</td>
                                    </tr>`;
                        $(".table tbody").append(html);
                    });

                }).fail(function(jqXHR, textStatus, errorThrown) {
                    // Handle the error here
                    console.log("Error: " + errorThrown);
                });

                event.preventDefault();
            });

            $('#downloadExcel').click(exportToExcel);

            function exportToExcel() {
                $('#table').table2excel({
                    exclude: ".no-export",
                    filename: "CustomerHistories.xls",
                    fileext: ".xls",
                    exclude_links: true,
                    exclude_inputs: true
                });
            }
        });
    </script>

    <script>
        // window.onscroll = function() {myFunction()};

        // var header = document.getElementById("myHeader");
        // var sticky = header.offsetTop;

        // function myFunction() {
        //   if (window.pageYOffset > sticky) {
        //     header.classList.add("sticky");
        //   } else {
        //     header.classList.remove("sticky");
        //   }
        // }
    </script>
</body>

</html>