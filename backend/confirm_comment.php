<?php 

	$return_datas = [];
	include("../config/config.php");
	session_start();

	$id = $_POST['item_id'];
	$query = "SELECT * FROM items WHERE id = '".$id."' ";
	// $result = $Mysqlconn->query($query);
	$result = pg_query($connections, $query);
	$response = pg_fetch_object($result);	

	// $chassino = $response->lastmachine_no;
	// $branchno = $response->branchno;
	// $status = $response->status;

	// $item_date = $response->created_at;

	// $dt = new DateTime($item_date);

	// $date = $dt->format('Y-m-d');
	// $time = $dt->format('H:i:s');

	// $ex_date = explode('-', $date);

	// $year = intval($ex_date[0]) + 543;

	// $qdate = $year.$ex_date[1].$ex_date[2];

	$return_datas['item'] = $response;

	echo json_encode($return_datas);

	include("../config/dbcloseconnect.php");

?>