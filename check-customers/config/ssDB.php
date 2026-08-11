<?php

	$conn_string = "host=203.150.187.72 port=5432 dbname=MotorBikeDBMS user=sa password=ecdssa3679cjk";
	$ssConnec = pg_connect($conn_string);

	if(!$ssConnec) {
	    echo 'database connected error';
	}

	// pg_close($connection);
?>