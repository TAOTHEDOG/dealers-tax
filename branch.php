<?php include "./config/ssDB.php"; ?>
<?php
session_start();
$branchgroup = $_SESSION['branchgroup'];

$sql = "select branchcode as cmpcode,branchname as cmpname,etax_group from branch";
// $sql = "select cmpcode,cmpname from tblcmpcode where etax_group = '{$branchgroup}'";
$query = pg_query($connection, $sql);
if (!$query) {
    echo "An error.\n $sql";
    exit;
}

$options = pg_fetch_all($query);
$options_json = json_encode($options, JSON_UNESCAPED_UNICODE);

echo ($options_json);
?>


<!-- $.ajax({
    url: "./backend/branch.php",
    type: "GET",
    dataType: "json",
    success: function(options) {
        $.each(options, function(index, element) {
        var option_element = $("<option></option>");
        option_element.val(element.cmpcode).text(element.cmpcode + '-' + element.cmpname);

        $($id).append(option_element);

        });
    }
}); -->