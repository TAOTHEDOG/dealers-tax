<?php
include("./config/config.php");
if (isset($_POST["username"]) && $_POST["username"] != "") {
	$uname = $_POST["username"];
	$pass = $_POST["password"];

	// $password = sha1($password);

	// Perform query

	if ($connections) {
		$query = "SELECT * FROM users WHERE username = '" . $uname . "' AND password = '" . $pass . "'";

		// $result = $Mysqlconn->query($query);
		$result = pg_query($connections, $query);

		// $row = $result->fetch_assoc();

		$rowcount = pg_num_rows($result);
		// echo $query;

		if ($rowcount > 0) {
			while ($row = pg_fetch_object($result)) {

				session_start();

				// Unset all of the session variables.
				$_SESSION = array();
				$branchgroup = $row->branchgroup;
				$name = $row->name;
				$role = (string)$row->role;
				$username = $row->username;
				$code = $row->code;

				// session_start();
				// ob_start();

				$_SESSION['name'] = $name;
				$_SESSION['username'] = $username;
				$_SESSION['role'] = $role;
				$_SESSION['branchgroup'] = $branchgroup;
				$_SESSION['code'] = $code;
				$_SESSION['dealer'] = $row->dealer;
				$_SESSION['id'] = $row->id;


				if ($role == "Dealer") {
					header("Location: form.php");
				} elseif ($role == "Accountant" || $role == "Admin" || $role == "CS") {
					header("Location: backend/index.php");
				} else {
					echo "<div style='text-align: center; padding-top: 15px;'><h3>Incorrect UserName or Password. Please try again. </h3><br> <a href='./login.php'>กลับหน้า login</a></div>";
					exit(0);
				}
			}
		} else {

			// header("location: login.php");
			echo "<div style='text-align: center; padding-top: 15px;'><h3>Incorrect UserName or Password. Please try again. </h3><br> <h3><a href='./login.php'>กลับหน้า login</a></h3></div>";
			exit(0);
		}
	}
} else {
	header("location: login.php");
	exit(0);
}

include("./config/dbcloseconnect.php");
