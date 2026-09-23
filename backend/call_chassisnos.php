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

// รูปภาพที่ผูกกับเลขที่ AP สำหรับแสดงใน Processing Modal
$return_datas['ap_pictures'] = [];
if ($response && !empty($response->ap_no)) {
	$pic_query = "SELECT filename, original_name FROM ap_pictures WHERE ap_no = $1 ORDER BY created_at";
	$pic_result = pg_query_params($connections, $pic_query, array($response->ap_no));
	if ($pic_result) {
		$return_datas['ap_pictures'] = pg_fetch_all($pic_result) ?: [];
	}
}

include("../config/ssDB.php");

$sql = "SELECT 
			d.chassisno,
			d.stockno AS icno,
			TO_CHAR(m.stockdate + INTERVAL '543 years', 'YYYY-MM-DD') AS icdate,
			b.apbillno AS apno,
			TO_CHAR(b.sdtduedate + INTERVAL '543 years', 'YYYY-MM-DD') AS apdate,
			con.contractno as con_contractno,
			TO_CHAR(con.contractdate + INTERVAL '543 years', 'YYYY-MM-DD')  as con_contractdate,
			hp.car_price as hp_car_price,
			d.grandtotal as d_grandtotal
		FROM mststocks m
		JOIN dtlstocks d 
			ON m.stockno = d.stockno
		LEFT JOIN hpcontracts_productdetails hp 
			ON hp.chassisno = d.chassisno
		LEFT JOIN contracts con 
			ON con.contractid = hp.contract_id
		LEFT JOIN LATERAL (
			SELECT b.apbillno, b.sdtduedate
			FROM dtlbillings a
			JOIN mstbillings b 
				ON a.apbillno = b.apbillno
			WHERE a.stockno = d.stockno
			LIMIT 1
		) b ON true
		WHERE m.stocktype IN ('IC', 'IO')
		AND d.chassisno LIKE '%$chassino%';";

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
