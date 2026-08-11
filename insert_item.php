
<?php 
	include("./config/config.php");
	session_start();
	if ($connections)
	{
		$machine_no = $_POST['machine_no'];
		$customername = $_POST['customername'];
		$branchno = $_POST['branchno'];
		$doc1 = '';
		$doc2 = '';
		$doc3 = '';
		$doc4 = '';
		$doc5 = '';

		if (isset($machine_no)) {	
			
			$branchgroup = $_SESSION['branchgroup'];
			$dealer_id =  $_SESSION['id'];
			$name =  $_SESSION['name'];
		
			// $upload1=$_FILES['picture1']['name'];
			// $size1 = $_FILES['picture1']['size'];

			if(isset($_FILES['picture1']) && $_FILES['picture1']['name'] != "") {
				// echo "file1<br>";
				// echo "<pre>";
				// print_r($_FILES['picture1']);

				$datenow = date("YmdHis");
				$numrand = (mt_rand(1000000,9999999));		

				$path="docs/";				
    			$typefile1 = strrchr($_FILES['picture1']['name'],".");
    			$newname1 = 'doc_'.$numrand.$datenow.$branchgroup.$typefile1;
    			$path_copy=$path.$newname1;

    			if (move_uploaded_file($_FILES['picture1']['tmp_name'],$path_copy) ) {
    				// $doc_name = $newname;
    				// echo "typefile<br>";
    				// echo $typefile;
    				// echo "upload file<br>";
    				// echo $doc_name;
    				$doc1 = $newname1;
    			}
			}
			if(isset($_FILES['picture2']) && $_FILES['picture2']['name'] != "") {			

				$datenow = date("YmdHis");
				$numrand = (mt_rand(1000000,9999999));			

				$path="docs/";				
    			$typefile2 = strrchr($_FILES['picture2']['name'],".");
    			$newname2 = 'doc_'.$numrand.$datenow.$branchgroup.$typefile2;
    			$path_copy=$path.$newname2;

    			if (move_uploaded_file($_FILES['picture2']['tmp_name'],$path_copy) ) {  				
    				$doc2 = $newname2;
    			}
			}
			if(isset($_FILES['picture3']) && $_FILES['picture3']['name'] != "") {			

				$datenow = date("YmdHis");
				$numrand = (mt_rand(1000000,9999999));	

				$path="docs/";				
    			$typefile3 = strrchr($_FILES['picture3']['name'],".");
    			$newname3 = 'doc_'.$numrand.$datenow.$branchgroup.$typefile3;
    			$path_copy=$path.$newname3;

    			if (move_uploaded_file($_FILES['picture3']['tmp_name'],$path_copy) ) { 				
    				$doc3 = $newname3;
    			}
			}
			if(isset($_FILES['picture4']) && $_FILES['picture4']['name'] != "") {				

				$datenow = date("YmdHis");
				$numrand = (mt_rand(1000000,9999999));	

				$path="docs/";				
    			$typefile4 = strrchr($_FILES['picture4']['name'],".");
    			$newname4 = 'doc_'.$numrand.$datenow.$branchgroup.$typefile4;
    			$path_copy=$path.$newname4;

    			if (move_uploaded_file($_FILES['picture4']['tmp_name'],$path_copy) ) {				
    				$doc4 = $newname4;
    			}
			}
			if(isset($_FILES['picture5']) && $_FILES['picture5']['name'] != "") {			

				$datenow = date("YmdHis");
				$numrand = (mt_rand(1000000,9999999));		

				$path="docs/";				
    			$typefile5 = strrchr($_FILES['picture5']['name'],".");
    			$newname5 = 'doc_'.$numrand.$datenow.$branchgroup.$typefile5;
    			$path_copy=$path.$newname5;

    			if (move_uploaded_file($_FILES['picture5']['tmp_name'],$path_copy) ) { 				
    				$doc5 = $newname5;
    			}
			}

			$query = "INSERT INTO items (lastmachine_no, customername, branchno,doc1,doc2,doc3,doc4,doc5, dealer_id,tax_no) VALUES ('$machine_no', '$customername', '$branchno', '$doc1', '$doc2', '$doc3', '$doc4', '$doc5', '$dealer_id','$name') ";

			// echo "query<br>";
			// echo $query;
			// echo "<br>";
			$result = pg_query($connections, $query);
			
			if ($result) {
				// echo $last_id;
				
				$message = "success";

				header("Location: form.php?message=$message");
			} else {
				$message = "Fail";

				header( "location: form.php?message=$message" );

	 			exit(0);
			}			

		} else {
			header( "location: login.php" );
	 		exit(0);
		}

	} else {
		header( "location: login.php" );
 		exit(0);
	}

	include("./config/dbcloseconnect.php");
?>

