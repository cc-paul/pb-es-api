<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$senderID   = $_POST["senderID"];
	$receiverID = $_POST["receiverID"];
	$chatID     = "";
	$error      = false;
    $message    = "";

    $stmt = $pdo->prepare("
    	SELECT 
    		chatID 
    	FROM 
    		es_message_room 
    	WHERE
    		(senderID = :senderID AND receiverID = :receiverID)
    	OR 
    		(receiverID = :receiverID AND senderID = :senderID)
    ");

    $stmt->bindParam(":senderID",$senderID,PDO::PARAM_STR);
    $stmt->bindParam(":receiverID",$receiverID,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {      
            $chatID = $row["chatID"];  
        }
        
        if ($chatID == null) {
            $chatID = date('YmdHis', time());

            $stmt_insert = $pdo->prepare("INSERT INTO es_message_room (chatID,senderID,receiverID) VALUES (:chatID,:senderID,:receiverID)");
		    $stmt_insert->bindParam(":chatID",$chatID,PDO::PARAM_STR);
		    $stmt_insert->bindParam(":senderID",$senderID,PDO::PARAM_STR);
		    $stmt_insert->bindParam(":receiverID",$receiverID,PDO::PARAM_STR);

		    if ($stmt_insert->execute()) {
		        $error   = false;
		        $message = "";
		    } else {
		        $error   = true;
		        $message = "Error creating conversation. Please try again later";
		    }
        }
    } else {
        $error = true;
        $message = "Error finding conversation. Please try again later";
    }

	$response["error"]   = $error;
    $response["message"] = $message;
    $response["chatID"]  = $chatID; 
    
    echo json_encode($response);
?>