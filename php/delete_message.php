<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$feedbackToID  = $_POST["feedbackToID"];
	$id            = $_POST["id"];

	$response      = array();
    $error         = false;
    $message       = "";
    $countComment  = 0; 
    $countSchedule = 0;

    $stmt_insert = $pdo->prepare("UPDATE es_messages SET isRemoved = 1 WHERE id = :id");
	$stmt_insert->bindParam(":id",$id,PDO::PARAM_STR);

	if ($stmt_insert->execute()) {
		
		$error    = false;
	    $message  = "Feedback has been deleted";

		$stmt = $pdo->prepare("
	        SELECT
				(SELECT
					COUNT(*)
				FROM
					es_schedule a 
				WHERE
					a.createdBy = :id
				AND
					MONTH(a.dateCreated) = MONTH(NOW())) AS totalSched,
				(SELECT
					COUNT(*)
				FROM
					es_messages a 
				WHERE
					a.feedbackToID = :id
				AND
					a.isRemoved = 0) AS totalFeedBack
	    ");

	    $stmt->bindParam(":id",$feedbackToID,PDO::PARAM_STR);

	    if ($stmt->execute()) {
	        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
	        $stmt->closeCursor();
	        
	        foreach($rcrd AS $row) {
	            $countSchedule  = $row["totalSched"];  
	            $countComment   = $row["totalFeedBack"];    
	        }
	    }

	} else {
	    $error    = true;
	    $message  = "Error deleting feedback";
	}

    $response["error"]         = $error;
    $response["message"]       = $message;
    $response["countComment"]  = $countComment;
    $response["countSchedule"] = $countSchedule;
    
    echo json_encode($response);
?>