<?php
$lastmachine_no = $_POST['lastmachine_no'];
$branchno = $_POST['branchno'];

include("config/config.php");
include("config/ssDB.php");

// หากลุ่มสาขา
$query = "select branchcode as cmpcode from branch where etax_group = (select etax_group from branch where branchcode = '$branchno' limit 1)";
// $query = "select cmpcode from tblcmpcode where etax_group = (select etax_group from tblcmpcode where cmpcode = '$branchno' limit 1)";
$result = pg_query($connection, $query);
$branchgroup = "";
while ($row = pg_fetch_object($result)) {
    $branchgroup .= "'" . $row->cmpcode . "',";
}

$branchgroup = substr($branchgroup,0,-1);


// หาเลขถังที่ซ้ำใน branchgroup
$query = "SELECT ic_no FROM items WHERE lastmachine_no = '$lastmachine_no' and branchno in ($branchgroup)";

$result = pg_query($connections, $query);
if(!$result) {
    echo $query;
}
while ($row = pg_fetch_object($result)) {
    $ic_no = $row->ic_no;
}

echo json_encode($ic_no);
