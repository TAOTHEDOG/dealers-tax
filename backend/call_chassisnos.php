<?php

$return_datas = [];
include("../config/config.php");
session_start();
$account_id = $_SESSION['id'];
$username = $_SESSION['username'];
$role = $_SESSION['role'];

$id = $_POST['item_id'];
$query = "SELECT * FROM items WHERE id = '" . $id . "' ";
$result = pg_query($connections, $query);
$response = pg_fetch_object($result);

$chassino = $response->lastmachine_no;

$return_datas['item'] = $response;

include("../config/ssDB.php");

$sql = "select d.chassisno,d.stockno as icno,to_char(m.stockdate + interval '543 years', 'YYYY-MM-DD') as icdate,
		(select b.apbillno from dtlbillings a,mstbillings b where a.apbillno = b.apbillno and a.stockno = d.stockno limit 1) as apno,
		(select to_char(b.sdtduedate + interval '543 years', 'YYYY-MM-DD') from dtlbillings a,mstbillings b where a.apbillno = b.apbillno and a.stockno = d.stockno limit 1) as apdate
		from mststocks m,dtlstocks d
		where m.stockno = d.stockno
		and m.stocktype in ('IC','IO')
		and d.chassisno like '%$chassino%'";

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
