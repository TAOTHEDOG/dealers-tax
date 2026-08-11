<?php include("./../../config/config.php"); ?>
<?php include("./../../config/ssDB.php"); ?>
<?php
header("Content-Type: application/vnd.ms-excel");
header('Content-Disposition: attachment; filename="รายงานส่งใบกำกับภาษี.xls"');

$branchfrom = $_GET['branchfrom'];
$branchto = $_GET['branchto'];
$datefrom = $_GET['datefrom'];
$dateto = $_GET['dateto'];
?>
<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">
<HTML>

<HEAD>
	<meta http-equiv="Content-type" content="text/html; charset=UTF-8" />
</HEAD>

<BODY>
	<?php

	echo "<style type='text/css'>table,p,tr,td{font-family:AngsanaUPC;font-size:20px;}</style>";
	echo "<body topmargin='0' leftmargin='0' onload='window.print()'>";

	echo "<table cellspacing='0' cellpadding='0' width='100%' border='0' class='cellpadding'><tr>";
	echo "<td width='10.0000%'align='left'>ลำดับ</td>";
	echo "<td width='10.0000%'align='left'>สาขา</td>";
	echo "<td width='10.0000%'align='left'>ชื่อสาขา</td>";
	echo "<td width='10.0000%'align='left'>วันที่ส่ง</td>";
	echo "<td width='10.0000%'align='left'>เวลาที่ส่ง</td>";
	echo "<td width='10.0000%'align='left'>เลขถัง 6 ตัว</td>";
	echo "<td width='10.0000%'align='left'>เลขถังเต็ม</td>";
	echo "<td width='10.0000%'align='left'>ชื่อลูกค้า</td>";
	echo "<td width='10.0000%'align='left'>VAT_NO</td>";
	echo "<td width='10.0000%'align='left'>VAT_DATE</td>";
	echo "<td width='10.0000%'align='left'>IC_NO</td>";
	echo "<td width='10.0000%'align='left'>IC_DATE</td>";
	echo "<td width='10.0000%'align='left'>AP_NO</td>";
	echo "<td width='10.0000%'align='left'>AP_DATE</td>";
	echo "<td width='10.0000%'align='left'>สถานะ</td>";
	echo "<td width='10.0000%'align='left'>หมายเหตุ</td>";
	echo "</tr></table>";


	$query = "SELECT i.branchno,i.created_at::DATE as created_date,i.created_at::TIME as created_time,i.machine_no,i.lastmachine_no,
	i.customername,i.vat_no,i.vat_date,i.ic_no,i.ic_date,i.ap_no,i.ap_date,i.status,i.comment
	FROM items i
	WHERE i.created_at::DATE between '$datefrom'::DATE and '$dateto'::DATE 
	and branchno between '$branchfrom' and '$branchto' 
	and i.status <> '7'  --ไม่เอาสถานะ cancel
	and (i.tax_no <> 'TEST      ' or i.tax_no is null)
	order by i.branchno,i.created_at::DATE desc";

	$result = pg_query($connections, $query);

	if (!$result) {
		echo $query;
		exit;
	}

	$l_no = 0;
	while ($row = pg_fetch_object($result)) {
		$query2 = "SELECT c.branchcode as cmpcode,c.branchname as cmpname from branch c where c.branchcode = '$row->branchno' limit 1";
		// $query2 = "SELECT c.cmpcode,c.cmpname from tblcmpcode c where c.cmpcode = '$row->branchno' limit 1";
		$result2 = pg_query($connection, $query2);
		if (!$result2) {
			echo $query2;
			exit;
		}
		while ($row2 = pg_fetch_object($result2)) {
			$cmpcode = $row2->cmpcode;
			$cmpname = $row2->cmpname;
		}

		if (!$result) {
			echo $query;
			exit;
		}

		switch ($row->status) {
			case 1:
				$status = 'pending';
				break;
			case 2:
				$status = 'processing';
				break;
			case 3:
				$status = 'processing';
				break;
			case 4:
				$status = 'Edit machineno';
				break;
			case 5:
				$status = 'paid';
				break;
			case 6:
				$status = 'reject';
				break;
		}

		$created_time = substr($row->created_time, 0, 8);

		$l_no++;
		echo "<table cellspacing='0' cellpadding='0' width='100%' border='0' class='cellpadding'><tr>";
		echo "<td width='10.0000%'align='left'>$l_no</td>";
		echo "<td width='10.0000%'align='left'>$cmpcode</td>";
		echo "<td width='10.0000%'align='left'>$cmpname</td>";
		echo "<td width='10.0000%'align='left'>" . fmdate($row->created_date) . "</td>";
		// echo "<td width='10.0000%'align='left'>$created_date</td>";
		echo "<td width='10.0000%'align='left'>$created_time</td>";
		echo "<td width='10.0000%'align='left'>$row->lastmachine_no</td>";
		echo "<td width='10.0000%'align='left'>$row->machine_no</td>";
		echo "<td width='10.0000%'align='left'>$row->customername</td>";
		echo "<td width='10.0000%'align='left'>$row->vat_no</td>";
		echo "<td width='10.0000%'align='left'>" . fmdate($row->vat_date) . "</td>";
		// echo "<td width='10.0000%'align='left'>$vat_date</td>";
		echo "<td width='10.0000%'align='left'>$row->ic_no</td>";
		echo "<td width='10.0000%'align='left'>" . fmdate($row->ic_date) . "</td>";
		echo "<td width='10.0000%'align='left'>$row->ap_no</td>";
		echo "<td width='10.0000%'align='left'>" . fmdate($row->ap_date) . "</td>";
		echo "<td width='10.0000%'align='left'>$status</td>";
		echo "<td width='10.0000%'align='left'>$row->comment</td>";
		echo "</tr></table>";
	}


	// แปลงวันที่เช่น 30/12/2566
	function fmdate($par)
	{
		$realdate = substr($par, 0, 10);
		$year = substr($realdate, 0, 4);

		if ($year == '1970') {
			return '';
		}
		if ($year <> '') {
			if ($year < '2500') {
				$year = intval($year) + 543;
			}
			$month = substr($realdate, 5, 2);
			$day = substr($realdate, 8, 2);

			return $day . "/" . $month . "/" . $year;
		}

		return '';
	}
	?>
</BODY>

</HTML>