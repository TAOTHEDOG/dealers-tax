<?php

	$conn_string = "host=192.168.1.19 port=11010 dbname=check-users user=cjk password=LIFZXCdv2jXPDV9";
	$localConnect = pg_connect($conn_string);

	if(!$localConnect) {
	    echo 'database connected error';
	}

    // pg_close($connection);
?>