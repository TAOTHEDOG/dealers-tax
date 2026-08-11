<?php

$return_datas = [];
include("../config/config.php");
session_start();
$account_id = $_SESSION['id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

$id = $_POST['item_id'];
$query = "SELECT * FROM items WHERE id = '" . $id . "' ";
// $result = $Mysqlconn->query($query);
$result = pg_query($connections, $query);
$response = pg_fetch_object($result);

$chassino = $response->lastmachine_no;
$branchno = $response->branchno;
$status = $response->status;
// while ($row = mysqli_fetch_object($result)) {
// }

if (($status == '1' || $status == '2' ) && $role <> 'CS') {
	// log start

	$comment  = "เริ่มดำเนินการรายการเลขถัง " . $chassino . " โดยผู้ใช้งานชื่อ " . $username;

	$query1 = "INSERT INTO acc_histories (account_id, state, item_id, comment) VALUES ('$account_id', 2, '$id', '$comment')";

	$result1 = pg_query($connections, $query1);
	$update_state = "UPDATE items SET status = '2' WHERE id = '" . $id . "' ";
	$result_update = pg_query($connections, $update_state);
}


$item_date = $response->created_at;

$dt = new DateTime($item_date);

$date = $dt->format('Y-m-d');
$time = $dt->format('H:i:s');

$ex_date = explode('-', $date);

$year = intval($ex_date[0]) + 543;

$qdate = $year . $ex_date[1] . $ex_date[2];

$return_datas['item'] = $response;

include("../config/ssDB.php");

$sql = "select d.chassisno,d.stockno as icno,m.stockdate as icdate,
		(select b.apbillno from dtlbillings a,mstbillings b where a.apbillno = b.apbillno and a.stockno = d.stockno limit 1) as apno,
		(select b.sdtduedate from dtlbillings a,mstbillings b where a.apbillno = b.apbillno and a.stockno = d.stockno limit 1) as apdate
		from mststocks m,dtlstocks d
		where m.stockno = d.stockno
		and m.stocktype in ('IC','IO')
		-- and m.branchno = '$branchno'
		and d.chassisno like '%$chassino%'
		-- and m.stockdate >= '$qdate' ";

$results = pg_query($connection, $sql);
$sf_datas = pg_fetch_all($results);

if (!empty($sf_datas)) {
	$return_datas['machine_datas'] = $sf_datas;
} else {
	$return_datas['machine_datas'] = [];
}

echo json_encode($return_datas);

include("../config/ssDBclose.php");

include("../config/dbcloseconnect.php");
