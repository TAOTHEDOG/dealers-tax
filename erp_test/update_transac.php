<?php
$conn_string = "host=203.150.199.7 port=5432 dbname=somjai_erp_prod_20260101 user=dev_surasak_readonly password=Kk5ZuB42GT45pASu";
$connection = pg_connect($conn_string);

if (!$connection) {
	echo 'database connected error';
}


// Fetch all stocknos first
$sql = "select stockno from temp_stockno limit 5000 offset 10000;";
$result = pg_query($connection, $sql);
$stocknos = [];
while ($row = pg_fetch_object($result)) {
    $stocknos[] = $row->stockno;
}


// Loop and update one by one
$count = 0;
foreach ($stocknos as $stockno) {
    $sql_update = "update mstdailytrans m set 
        debitamount = (select sum(amount) from dtlmstdailytrans where mstdailytran_id = m.mstdailytranid and is_debit = true and inused = true and deleted_at is null),
        credcreditamount = (select sum(amount) from dtlmstdailytrans where mstdailytran_id = m.mstdailytranid and is_credit = true and inused = true and deleted_at is null)
        from mststocks ms
        where (m.mstdailytranno = ms.stockno or m.journalno = ms.stockno) and ms.stockno = '$stockno';";
    
    pg_query($connection, $sql_update);
    $count++;
    echo $count . ' : ' . $stockno . "\n";
    flush(); // show progress in real time
}