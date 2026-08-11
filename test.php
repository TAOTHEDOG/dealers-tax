
<?php
// $branchgroup = $_GET['$branchgroup'];
$conn_string = "host=203.150.187.72 port=5432 dbname=MotorBikeDBMS user=sa password=ecdssa3679cjk";
$connection = pg_connect($conn_string);

if (!$connection) {
    echo 'database connected error';
} else {
    echo 'database connected';
}
// $sql  = "elect cmpcode,cmpname from tblcmpcode where branchgroup = '$branchgroup' order by cmpcode";
// if (!$tbl) {
//     echo "{'status':'02','msg':'execution error.'}";
//     exit;
// }

// $returns = "{'status':'00','msg':'Completed insert new data.'}";
// pg_close($conn);

// echo $returns;
exit;
?>
