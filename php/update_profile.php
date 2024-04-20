<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id             = $_POST["id"];
	$mobileNumber   = $_POST["mobileNumber"];
	$provinceID     = $_POST["provinceID"];
	$municipalityID = $_POST["municipalityID"];
	$barangayID     = $_POST["barangayID"];
	$streetName     = $_POST["streetName"];
	$serviceIDs     = explode(',', $_POST["serviceIDs"]); 
	$serviceIDs2    = $_POST["serviceIDs2"];

	$response = array();
    $error    = false;
    $message  = "";


    $stmt_insert = $pdo->prepare("UPDATE es_appaccount_registration SET mobileNumber=:mobileNumber,provinceID=:provinceID,municipalityID=:municipalityID,barangayID=:barangayID,streetName=:streetName WHERE id=:id");
	$stmt_insert->bindParam(":mobileNumber",$mobileNumber,PDO::PARAM_STR);
	$stmt_insert->bindParam(":provinceID",$provinceID,PDO::PARAM_STR);
	$stmt_insert->bindParam(":municipalityID",$municipalityID,PDO::PARAM_STR);
	$stmt_insert->bindParam(":barangayID",$barangayID,PDO::PARAM_STR);
	$stmt_insert->bindParam(":streetName",$streetName,PDO::PARAM_STR);
	$stmt_insert->bindParam(":id",$id,PDO::PARAM_STR);

	if ($stmt_insert->execute()) {
		if ($serviceIDs2 != '~') {
			$queryServiceID = array();

		    foreach($serviceIDs as $serviceID) {
		    	array_push($queryServiceID, "(".$id.",".$serviceID.")");
			}

			// $stmt_insert = $pdo->prepare('DELETE FROM es_appaccount_services WHERE serviceID = 0');
			// $stmt_insert->execute();


			$stmt_insert = $pdo->prepare('DELETE FROM es_appaccount_services WHERE userID='.$id);
			if ($stmt_insert->execute()) {

				$stmt_insert = $pdo->prepare('INSERT INTO es_appaccount_services (userID,serviceID) VALUES ' . join(",",$queryServiceID));
				if ($stmt_insert->execute()) {
					$error   = false;
	            	$message = "Profile has been updated";
				} else {
					$error   = true;
	            	$message = "Error updating profile : " . join(",",$queryServiceID);
				}


			} else {
				$error   = true;
				$message = "Error resetting services";
			}
		} else {
			$error   = false;
	        $message = "Profile has been updated1";
		}
	} else {
	    $error   = true;
	    $message = "Error saving account details";
	}


    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result;
    
    echo json_encode($response);
?>