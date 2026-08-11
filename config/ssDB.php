<?php
	//Code Old Before connect ss
	// $conn_string = "host=192.168.199.1 port=5432 dbname=MotorBikeDBMS user=sa password=ecdssa3679cjk";
	// $connection = pg_connect($conn_string);

	// if(!$connection) {
	//     echo 'database connected error';
	// }

	// $result = pg_query($connection, "select * from tblcustomer limit 10");
	// // var_dump(pg_fetch_all($result));

	// $result = pg_fetch_all($result);

	// for ($i=0; $i < count($result); $i++) { 
	// 	var_dump($result[$i]['customercode']);
	// }

	// pg_close($connection);

?>

<?php
//Connect ERP
	$conn_string = "host=203.150.199.7 port=5432 dbname=somjai_erp_prod_20260101 user=dev_surasak_readonly password=Kk5ZuB42GT45pASu";
	$connection = pg_connect($conn_string);

	if(!$connection) {
	    echo 'database connected error';
	}

	// $result = pg_query($connection, "select * from tblcustomer limit 10");
	// // var_dump(pg_fetch_all($result));

	// $result = pg_fetch_all($result);

	// for ($i=0; $i < count($result); $i++) { 
	// 	var_dump($result[$i]['customercode']);
	// }

	// pg_close($connection);

?>