<?php
include("./config/config.php");
$id = $_GET['id'];

$query = "update items set status = '7' where id = $id";
$result = pg_query($connections, $query);

header('Location: ./view.php');