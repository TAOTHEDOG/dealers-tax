<?php 

	
	$item_id= $_POST['item_id'];
	$lastmachine_no= $_POST['lastmachine_no'];

	include("../config/ssDB.php");

		$sql = "select m.vatno,to_char(m.vatdate + interval '543 years', 'YYYY-MM-DD') as vatdate,d.chassisno
	from mststocks m,dtlstocks d
	where m.stockno = d.stockno and m.stocktype = 'IC'
	and chassisno like'%$lastmachine_no'
	limit 1";
	// $sql = "select m.vatno,m.vatdate,d.chassisno
	// from tblmststock m,tbldtlstock d
	// where m.stockno = d.stockno and m.stocktype = 'IC'
	// and chassisno like'%$lastmachine_no'
	// limit 1";

	$results = pg_query($connection, $sql);
	// var_dump(pg_fetch_all($result));

	$return_datas = pg_fetch_all($results);

	// var_dump($result);

	echo json_encode($return_datas);

	include("../config/ssDBclose.php");
	// echo json_encode($lastmachine_no);

?>

