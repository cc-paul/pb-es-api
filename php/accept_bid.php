<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id           = $_POST["id"];
	$notifMessage = $_POST["message"];

	$response = array();
    $error    = false;
    $message  = "";


    $stmt_insert = $pdo->prepare("UPDATE es_bid SET isAccepted = 1 WHERE id=:id");
	$stmt_insert->bindParam(":id",$id,PDO::PARAM_STR);

	if ($stmt_insert->execute()) {
	   	$stmt_insert = $pdo->prepare("
            INSERT INTO es_notification (
                message,
                receiverID,
                dateCreated,
                taskID
            )
            SELECT
                :message,
                userID,
                :dateCreated,
                taskID
            FROM
                es_bid
            WHERE 
                id = :id
        ");
        $stmt_insert->bindParam(":message",$notifMessage,PDO::PARAM_STR);
        $stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);
        $stmt_insert->bindParam(":id",$id,PDO::PARAM_STR);

        if ($stmt_insert->execute()) {
            $error   = false;
            $message = "Bid has been accepted. Congratulations!";
        } else {
            $error   = true;
            $message = "Error saving notification but the bid has been accepted";
        }
	} else {
	    $error   = true;
	    $message = "Error accepting bid";
	}

	$response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>