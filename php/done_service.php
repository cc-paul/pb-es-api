<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$taskID = $_POST["taskID"];
	$doneBy = $_POST["doneBy"];

	$response  = array();
    $error     = false;
    $message   = "";
    $countDone = 0;


    $stmt_insert = $pdo->prepare("UPDATE es_schedule SET isServiceDone = 1,doneBy = :doneBy WHERE id = :taskID");
	$stmt_insert->bindParam(":doneBy",$doneBy,PDO::PARAM_STR);
	$stmt_insert->bindParam(":taskID",$taskID,PDO::PARAM_STR);

	if ($stmt_insert->execute()) {
	   	$message = "Task has been finished successfully";

	   	$sql = "SELECT * FROM es_schedule WHERE isServiceDone = 1 AND IFNULL(doneBy,'') = $doneBy";

	    $stmt = $pdo->prepare($sql);
	   	if ($stmt->execute()) {
	        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
	        $stmt->closeCursor();
	        
	        foreach($rcrd AS $row) {
	            $countDone++;
	        }
	    }

	} else {
	    $error   = true;
	    $message = "Error finishing task";
	}

	$response["error"]     = $error;
    $response["message"]   = $message;
    $response["countDone"] = $countDone;
    
    echo json_encode($response);
?>