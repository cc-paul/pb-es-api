<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$postID    = $_POST["postID"];
	$posterID  = $_POST["posterID"];
	$comment   = $_POST["comment"];
	$bid       = $_POST["bid"];
	$createdBy = $_POST["createdBy"];
	$isYours   = $_POST["isYours"];
	$commentorName = $_POST["commentorName"];
	$notifMessage = $commentorName . " commented on your post";

	$response     = array();
    $error        = false;
    $message      = "";
    $countComment = 0;


    $stmt_insert = $pdo->prepare("INSERT INTO es_customer_post_comment (postID,posterID,comment,createdBy,dateCreated,bidAmount) VALUES (:postID,:posterID,:comment,:createdBy,:dateCreated,:bidAmount)");
	$stmt_insert->bindParam(":postID",$postID,PDO::PARAM_STR);
	$stmt_insert->bindParam(":posterID",$posterID,PDO::PARAM_STR);
	$stmt_insert->bindParam(":comment",$comment,PDO::PARAM_STR);
	$stmt_insert->bindParam(":createdBy",$createdBy,PDO::PARAM_STR);
	$stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);
	$stmt_insert->bindParam(":bidAmount",$bid,PDO::PARAM_STR);

	if ($stmt_insert->execute()) {
		
		$error    = false;
	    $message  = "Comment has been posted";

		$stmt = $pdo->prepare("
	        SELECT 
				COUNT(*) AS countComments
			FROM	
				es_customer_post_comment a 
			WHERE
				a.isRemoved = 0 
			AND 
				a.postID = :postID
	    ");

	    $stmt->bindParam(":postID",$postID,PDO::PARAM_STR);

	    if ($stmt->execute()) {
	        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
	        $stmt->closeCursor();
	        
	        foreach($rcrd AS $row) {
	            $countComment = $row["countComments"];
	        }
	    }

	    if ($isYours == 0) {
	   		$stmt_insert = $pdo->prepare("INSERT INTO es_notification (message,receiverID,dateCreated,taskID) VALUES (:message,:receiverID,:dateCreated,0)");
			$stmt_insert->bindParam(":message",$notifMessage,PDO::PARAM_STR);
			$stmt_insert->bindParam(":receiverID",$posterID,PDO::PARAM_STR);
			$stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);
			$stmt_insert->execute();
	    }

	} else {
	    $error    = true;
	    $message  = "Error giving comment";
	}

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["countComment"] = $countComment;
    
    echo json_encode($response);
?>