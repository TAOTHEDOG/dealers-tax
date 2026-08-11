<?php 
	include("../config/config.php");
	session_start();

	$id = $_POST['edit_item_id'];
	$edit_machineno = $_POST['edit_machineno'];
	$correct_machine = $_POST['correct_machine'];

	// log
	$account_id = $_SESSION['id'];
	$username = $_SESSION['username'];
	
	$comment_log  = "แก้ไขรายการเลขถังจาก ".$edit_machineno." เป็น ".$correct_machine." โดยผู้ใช้งานชื่อ ".$username;

	$query1 = "INSERT INTO acc_histories (account_id, state, item_id, comment) VALUES ('$account_id', 4, '$id', '$comment_log')";

	$result1 = pg_query($connections, $query1);

	// update Reject, ic_no = '".$_POST['select_icno']."'
	$update_state = "UPDATE items SET status = 4, machine_no = '$correct_machine' WHERE id = '".$id."' ";
	$result_update = pg_query($connections, $update_state);

	$message = "แก้ไขรายการเลขถังสำเร็จ";
	header( "location: index.php?message=$message" );
 	exit(0);

	include("../config/dbcloseconnect.php");
?>