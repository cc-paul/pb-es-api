<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$feedbackToID = $_POST["feedbackToID"];
	$feedbackByID = $_POST["feedbackByID"];
	$feedback     = $_POST["feedback"];

	$response      = array();
    $error         = false;
    $message       = "";
    $countComment  = 0; 
    $countSchedule = 0;

    $stmt_insert = $pdo->prepare("INSERT INTO es_messages (feedbackToID,feedbackByID,feedback,dateCreated) VALUES (:feedbackToID,:feedbackByID,:feedback,:dateCreated)");
	$stmt_insert->bindParam(":feedbackToID",$feedbackToID,PDO::PARAM_STR);
	$stmt_insert->bindParam(":feedbackByID",$feedbackByID,PDO::PARAM_STR);
	$stmt_insert->bindParam(":feedback",$feedback,PDO::PARAM_STR);
	$stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);

	if ($stmt_insert->execute()) {
		
		$error    = false;
	    $message  = "Feedback has been sent";

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
	    $message  = "Error giving feedback";
	}

    $response["error"]         = $error;
    $response["message"]       = $message;
    $response["countComment"]  = $countComment;
    $response["countSchedule"] = $countSchedule;
    
    echo json_encode($response);
?>