<?php

include("../config/ssDB.php");

include("../config/config.php");

$query = "SELECT * FROM items WHERE status in ('3','4')";

$result = pg_query($connections, $query);

$rowcount = pg_num_rows($result);

if ($rowcount > 0) {
	$apno = "";
	$apdate = NULL;
	// $icno = "";
	// $icdate = NULL;
	$vatno = "";
	$vatdate = NULL;

	while ($row = pg_fetch_object($result)) {
		$item_id = $row->id;
		$lastmachine_no = $row->lastmachine_no;
		$branchno = $row->branchno;
		$chassisno = $row->machine_no;

		$sql_update = "select d.chassisno,d.stockno as icno,to_char(m.stockdate + interval '543 years', 'YYYY-MM-DD') as icdate, m.branchno,
						m.vatno,to_char(m.vatdate + interval '543 years', 'YYYY-MM-DD') as vatdate,
						(select b.apbillno from dtlbillings a,mstbillings b where a.mstbilling_id = b.mstbillingid and a.stockno = d.stockno and b.inused = true limit 1) as apno,
						(select to_char(b.due_date + interval '543 years', 'YYYY-MM-DD') from dtlbillings a,mstbillings b where a.mstbilling_id = b.mstbillingid and a.stockno = d.stockno and b.inused = true limit 1) as apdate,
						(select b.nettotal from dtlbillings a,mstbillings b where a.mstbilling_id = b.mstbillingid and a.stockno = d.stockno and b.inused = true limit 1) as aptotal
						from mststocks m,dtlstocks d
						where m.mststockid = d.mststock_id
						and m.stocktype in ('IC','IO') and m.inused = true
						and m.branch_owner_code in (select branchcode from branch where etax_group = (select etax_group from branch where branchcode = '$branchno' limit 1))
						and d.chassisno = '$chassisno'
						-- and m.branch_owner_code in (select branchcode from branch where etax_group = (select etax_group from branch where branchcode = '895' limit 1))
						-- and d.chassisno = 'MLHJK0430S5652414'
						 ";

		$resuquery = pg_query($connection, $sql_update);
		$result2 = pg_fetch_object($resuquery);
		$result2_row = pg_num_rows($resuquery);

		if ($result2_row > 0) {
			$apno = $result2->apno;
			$apdate = $result2->apdate;
			$aptotal = $result2->aptotal ?? 0;

			$vatno = $result2->vatno;
			$vatdate = $result2->vatdate;


			if (isset($apno) && isset($apdate)) {
				$getapdate = strtotime($apdate);
				$ap_date = date('Y-m-d', $getapdate);
				$query4 = "UPDATE items SET ap_no = '$apno', ap_date = '$ap_date', ap_total = $aptotal, status = '5' WHERE id = '$item_id' ";

				$result4 = pg_query($connections, $query4);
			}

			if (isset($vatno) && isset($vatdate)) {
				$date = strtotime($vatdate);
				$vat_date = date('Y-m-d', $date);

				$query5 = "UPDATE items SET vat_no = '$vatno', vat_date = '$vat_date' WHERE id = '$item_id' ";
				$result5 = pg_query($connections, $query5);
			}
		}
	}
}

include("../config/dbcloseconnect.php");

include("../config/ssDBclose.php");

header('Location: ./index.php');
