<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$optionID = $_POST["optionID"];
	$userID   = $_POST["userID"];
	$imageID  = $_POST["imageID"];

	$response = array();
    $error    = false;
    $message  = "";


    switch ($optionID) {
    	case 0:
    		
    		/* Set as profile picture */
			$stmt_insert = $pdo->prepare("UPDATE es_appaccount_ids SET isProfile = 0 WHERE userID = :userID");
            $stmt_insert->bindParam(":userID",$userID,PDO::PARAM_STR);

            if ($stmt_insert->execute()) {
                $stmt_insert = $pdo->prepare("UPDATE es_appaccount_ids SET isProfile = 1 WHERE id = :imageID");
	            $stmt_insert->bindParam(":imageID",$imageID,PDO::PARAM_STR);

	            if ($stmt_insert->execute()) {
	                $error   = false;
	                $message = "Profile has been updated";
	            } else {
	                $error   = true;
	                $message = "Error updating password";
	            }
            } else {
                $error   = true;
                $message = "Error resetting profile";
            }


    		break;

    	case 1:

    		/* Delete Photo */
    		$stmt = $pdo->prepare("SELECT * FROM es_appaccount_ids WHERE isProfile = 1 AND id = :imageID");
		    $stmt->bindParam(":imageID",$imageID,PDO::PARAM_STR);

		    if ($stmt->execute()) {
		        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
		        $stmt->closeCursor();
		        $count = 0;
		        
		        foreach($rcrd AS $row) {
		            $count++;
		        }
		        
		        if ($count == 0) {
		            $stmt_insert = $pdo->prepare("UPDATE es_appaccount_ids SET isDeleted = 1 WHERE id = :imageID");
		            $stmt_insert->bindParam(":imageID",$imageID,PDO::PARAM_STR);

		            if ($stmt_insert->execute()) {
		                $error   = false;
		                $message = "Image has been deleted";
		            } else {
		                $error   = true;
		                $message = "Error deleting image";
		            }
		        } else {
		        	$error = true;
		        	$message = "Unable to delete. This is your current profile picture";
		        }
		    } else {
		        $error = true;
		        $message = "Error checking photo to be deleted";
		    }


    		break;
    }


    $response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>