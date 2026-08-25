<?php 
	
	include("../config/config.php");
	session_start();

	$id = $_POST['item_id'];
	$reject_machineno = $_POST['reject_machineno'];
	$comment = $_POST['comment'];

	// log
	$account_id = $_SESSION['id'];
	$username = $_SESSION['username'];
	
	$comment_log  = "Reject รายการเลขถัง ".$reject_machineno." โดยผู้ใช้งานชื่อ ".$username;

	$query1 = "INSERT INTO acc_histories (account_id, state, item_id, comment) VALUES ('$account_id', 6, '$id', '$comment_log')";

	// $result1 = $Mysqlconn->query($query1);
	$result1 = pg_query($connections, $query1);

	// update Reject, ic_no = '".$_POST['select_icno']."'
	$update_state = "UPDATE items SET status = 6, comment = '$comment' WHERE id = '".$id."' ";
	// $result_update = $Mysqlconn->query($update_state);
	$result_update = pg_query($connections, $update_state);

	$message = "Reject รายการสำเร็จ";
	header( "location: index.php?message=$message" );
 	exit(0);

	include("../config/dbcloseconnect.php");
?>