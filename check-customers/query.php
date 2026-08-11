<?php require("./config/ssDB.php"); ?>
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $idCard = $_POST['idCard'];
    $phoneNo = $_POST['phoneNo'];
    $firstname = $_POST['firstname'];
    $lastname = $_POST['lastname'];

    $homeno = $_POST['homeno'];
    $moo = $_POST['moo'];
    $soi = $_POST['soi'];
    $tanann = $_POST['tanann'];
    $tambol = $_POST['tambol'];
    $amphon = $_POST['amphon'];
    $province = $_POST['province'];
    $postcode = $_POST['postcode'];

    $rowData = array();

    //-----------------------ผู้ซื้อ----------------------//

    $query = "SELECT ('ผู้ซื้อ') as customer_type,h.contractno,h.contractdate,c.customercode,(c.prefix||' '||c.arname||' '||c.lname) as arname,
            (select netprice from tbldtlstock where stockno = h.saleno) as netprice,
            h.hploannet,h.hp_paidamount,h.hp_overdueperiod,h.hp_overdueamt,((h.hploannet + h.hpdown) - (h.hp_paidamount + h.dn_paidamount)) AS hpbalance,
            (select financecode from tblmststock where stockno = h.saleno) as financecode,h.closed,h.closedtype,h.customlevel,c.blacklisttype
    -- ,c.phoneno,c.mobileno,c.taxid,
    -- ('บ้านเลขที่ '||c.homeno||' หมู่ '||c.moo||' ซอย '||c.soi||' ถนน '||c.tanann||' ตำบล '||c.tambol||' อำเภอ '||c.amphon||' จังหวัด '||c.province||' หรัสไปรษณีย์ '||c.postcode) as address
    from tblcustomer c,tblhpcontract h
    where c.customercode = h.customercode
    and c.taxid like '%$idCard%'
    and (c.phoneno like '%$phoneNo%' or c.mobileno like '%$phoneNo%')
    and c.arname like '%$firstname%'
    and c.lname like '%$lastname%'
    and c.homeno like '%$homeno%'
    and c.moo like '%$moo%'
    and c.soi like '%$soi%'
    and c.tanann like '%$tanann%'
    and c.tambol like '%$tambol%'
    and c.amphon like '%$amphon%'
    and c.province like '%$province%'
    and c.postcode like '%$postcode%'
    order by h.contractdate desc limit 100";

    $result = pg_query($ssConnec, $query);

    while ($row = pg_fetch_object($result)) {
        $subRowData = array();
        $contractdate = substr($row->contractdate, 6, 2) . '/' . substr($row->contractdate, 4, 2) . '/' . substr($row->contractdate, 0, 4);
        array_push($subRowData, $row->customer_type);
        array_push($subRowData, $row->contractno);
        array_push($subRowData, $contractdate);
        array_push($subRowData, $row->customercode);
        array_push($subRowData, $row->arname);

        array_push($subRowData, $row->netprice);
        array_push($subRowData, $row->hploannet);
        array_push($subRowData, $row->hp_paidamount);
        array_push($subRowData, $row->hp_overdueperiod);
        array_push($subRowData, $row->hp_overdueamt);
        array_push($subRowData, $row->hpbalance);
        array_push($subRowData, $row->financecode);
        array_push($subRowData, $row->closed);
        array_push($subRowData, $row->closedtype);
        array_push($subRowData, $row->customlevel);
        array_push($subRowData, $row->blacklisttype);


        array_push($rowData, $subRowData);
    }

    //-----------------------ผู้ค้ำ----------------------//

    $query = "SELECT (case when g.seqno = 1 then 'ผู้ค้ำ1'
                           when g.seqno = 2 then 'ผู้ค้ำ2'
                           when g.seqno = 5 then 'ผู้ซื้อร่วม' end)  as customer_type,g.requestno as contractno,
    h.contractdate,c.customercode,
    (c.prefix||' '||c.arname||' '||c.lname) as arname,
    (select netprice from tbldtlstock where stockno = h.saleno) as netprice,
    h.hploannet,h.hp_paidamount,h.hp_overdueperiod,h.hp_overdueamt,((h.hploannet + h.hpdown) - (h.hp_paidamount + h.dn_paidamount)) AS hpbalance,
    (select financecode from tblmststock where stockno = h.saleno) as financecode,h.closed,h.closedtype,h.customlevel,c.blacklisttype
    -- ,c.phoneno,c.mobileno,c.taxid,
    -- ('บ้านเลขที่ '||c.homeno||' หมู่ '||c.moo||' ซอย '||c.soi||' ถนน '||c.tanann||' ตำบล '||c.tambol||' อำเภอ '||c.amphon||' จังหวัด '||c.province||' หรัสไปรษณีย์ '||c.postcode) as address
    from tblcustomer c,tblrequestgurantee g left join tblhpcontract h on h.contractno = g.requestno
    where c.customercode = g.gidcard and g.seqno in (1,2,5)
    and c.taxid like '%$idCard%'
    and (c.phoneno like '%$phoneNo%' or c.mobileno like '%$phoneNo%')
    and c.arname like '%$firstname%'
    and c.lname like '%$lastname%'
    and c.homeno like '%$homeno%'
    and c.moo like '%$moo%'
    and c.soi like '%$soi%'
    and c.tanann like '%$tanann%'
    and c.tambol like '%$tambol%'
    and c.amphon like '%$amphon%'
    and c.province like '%$province%'
    and c.postcode like '%$postcode%'
    limit 100";

    $result = pg_query($ssConnec, $query);

    while ($row = pg_fetch_object($result)) {
        $subRowData = array();
        $contractdate = substr($row->contractdate, 6, 2) . '/' . substr($row->contractdate, 4, 2) . '/' . substr($row->contractdate, 0, 4);
        array_push($subRowData, $row->customer_type);
        array_push($subRowData, $row->contractno);
        array_push($subRowData, $contractdate);
        array_push($subRowData, $row->customercode);
        array_push($subRowData, $row->arname);

        array_push($subRowData, $row->netprice);
        array_push($subRowData, $row->hploannet);
        array_push($subRowData, $row->hp_paidamount);
        array_push($subRowData, $row->hp_overdueperiod);
        array_push($subRowData, $row->hp_overdueamt);
        array_push($subRowData, $row->hpbalance);
        array_push($subRowData, $row->financecode);
        array_push($subRowData, $row->closed);
        array_push($subRowData, $row->closedtype);
        array_push($subRowData, $row->customlevel);
        array_push($subRowData, $row->blacklisttype);

        array_push($rowData, $subRowData);
    }


    //-----------------------ไม่มีข้อมูลซื้อ----------------------//

    $query = "SELECT ('') as customer_type,('') as contractno,('') as contractdate,c.customercode,(c.prefix||' '||c.arname||' '||c.lname) as arname,
    ('') as netprice,
    ('') as hploannet,('') as hp_paidamount,('') as hp_overdueperiod,('') as hp_overdueamt,
    ('') as hpbalance,
    ('') as financecode,('') as closed,('') as closedtype,('') as customlevel,c.blacklisttype
-- ,c.phoneno,c.mobileno,c.taxid,
-- ('บ้านเลขที่ '||c.homeno||' หมู่ '||c.moo||' ซอย '||c.soi||' ถนน '||c.tanann||' ตำบล '||c.tambol||' อำเภอ '||c.amphon||' จังหวัด '||c.province||' หรัสไปรษณีย์ '||c.postcode) as address

    from tblcustomer c
    where customercode not in (select customercode from tblhpcontract where customercode = c.customercode)
    and customercode not in (select gidcard from tblrequestgurantee where gidcard = c.customercode and seqno in (1,2,5))
    and c.taxid like '%$idCard%'
    and (c.phoneno like '%$phoneNo%' or c.mobileno like '%$phoneNo%')
    and c.arname like '%$firstname%'
    and c.lname like '%$lastname%'
    and c.homeno like '%$homeno%'
    and c.moo like '%$moo%'
    and c.soi like '%$soi%'
    and c.tanann like '%$tanann%'
    and c.tambol like '%$tambol%'
    and c.amphon like '%$amphon%'
    and c.province like '%$province%'
    and c.postcode like '%$postcode%'
    limit 100";

    $result = pg_query($ssConnec, $query);

    while ($row = pg_fetch_object($result)) {
        $subRowData = array();
        $contractdate = substr($row->contractdate, 6, 2) . '/' . substr($row->contractdate, 4, 2) . '/' . substr($row->contractdate, 0, 4);
        array_push($subRowData, $row->customer_type);
        array_push($subRowData, $row->contractno);
        array_push($subRowData, $contractdate);
        array_push($subRowData, $row->customercode);
        array_push($subRowData, $row->arname);

        array_push($subRowData, $row->netprice);
        array_push($subRowData, $row->hploannet);
        array_push($subRowData, $row->hp_paidamount);
        array_push($subRowData, $row->hp_overdueperiod);
        array_push($subRowData, $row->hp_overdueamt);
        array_push($subRowData, $row->hpbalance);
        array_push($subRowData, $row->financecode);
        array_push($subRowData, $row->closed);
        array_push($subRowData, $row->closedtype);
        array_push($subRowData, $row->customlevel);
        array_push($subRowData, $row->blacklisttype);

        array_push($rowData, $subRowData);
    }


    $response = array(
        'success' => true,
        'message' => 'Data received and processed successfully.',
        'rowData' => $rowData,
    );
} else {
    $response = array(
        'success' => false,
        'message' => 'Invalid request method. This endpoint only accepts POST requests.',
    );
}

header('Content-Type: application/json');
echo json_encode($response);
