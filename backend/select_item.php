<?php 
	
	include("../config/config.php");
	session_start();

	// echo "Select Item id<br>";
	// echo $_POST['item_id'];
	// echo "<br>";
	// echo "Select Machine no<br>";
	// echo $_POST['select_machineno'];
	// echo "<br>Select IC no<br>";
	echo $_POST['select_icno'];
	// echo "<br>Select IC date<br>";
	echo $_POST['select_icdate'];

	$item_id = $_POST['item_id'];
	$account_id = $_SESSION['id'];
	$username = $_SESSION['username'];
	$lastmachine_no =  $_POST['lastmachine_no'];
	$ic_no = $_POST['select_icno'];
	$ic_date = $_POST['select_icdate'];
	// echo "Select account id<br>";
	// echo $account_id;

	$icdate = null;
	$apdate = null;

	if (isset($_POST['select_machineno']) && isset($_POST['select_icno'])) {

		$date = strtotime($_POST['select_icdate']);

		$icdate = date('Y-m-d',$date);

		$fapdate = strtotime($_POST['select_apdate']);
		$apdate = date('Y-m-d',$fapdate);

		$query = "UPDATE items SET machine_no = '".$_POST['select_machineno']."', ic_no = '".$_POST['select_icno']."', ic_date = '$icdate', ap_no = '".$_POST['select_apno']."', ap_date = '$apdate', status = 3 WHERE id = '$item_id' ";

		// echo "qu<br>";
		// echo $query;
		$result = pg_query($connections, $query);

		$comment  = "อัพเดทรายการเลขถังจาก ".$lastmachine_no." เปฺ็น ".$_POST['select_machineno']." และเลือกใบรับรถ (ic no) เป็น ".$ic_no." โดยผู้ใช้งานชื่อ ".$username;

		$query1 = "INSERT INTO acc_histories (account_id, state, item_id, comment) VALUES ('$account_id', 3, '$item_id', '$comment')";

		$result1 = pg_query($connections, $query1);
		// echo "qu<br>";
		// echo $query1;

		$message = "เลือกรายการสำเร็จ";
		header( "location: index.php?message=$message" );
 		exit(0);

	}

	include("../config/dbcloseconnect.php");
?>