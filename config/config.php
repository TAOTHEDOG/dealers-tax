<?php

//ตัวอย่างไวยากรณ์การเชื่อมต่อฐานข้อมูลสำหรับ PHP และ MySQL

//เชื่อมต่อกับฐานข้อมูล

// $hostname = "localhost";
// $database = "dealconacc";
// $username = "root";
// $password = "";

// // Create connection

// $Mysqlconn = mysqli_connect($hostname, $username, $password, $database);
// // $Mysqlconn = new mysqli($hostname,$username,$password,$database);


// // Check connection

// if (!$Mysqlconn) {

//     die("Connection failed: " . mysqli_connect_error());
//  	// $mysqli = new mysqli("localhost","my_user","my_password","my_db");
// }
// // echo "Connected successfully";

$conn_string = "host=192.168.1.19 port=11010 dbname=cjk user=cjk password=LIFZXCdv2jXPDV9";
$connections = pg_connect($conn_string);

if (!$connections) {
	echo 'database connected error';
}

$hostname = "https://dealers-tax.imax.dev";

date_default_timezone_set("Asia/Bangkok");

?>