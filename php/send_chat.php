<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$senderID        = $_POST["senderID"];
	$chatID          = $_POST["chatID"];
	$currentMessage  = $_POST["message"];
	$error           = false;
    $message         = "";
    $imageLink       = "-";
    $id              = 0;


    $stmt_insert = $pdo->prepare("INSERT INTO es_message_chat (chatID,message,senderID,dateCreated) VALUES (:chatID,:message,:senderID,:dateCreated)");
    $stmt_insert->bindParam(":chatID",$chatID,PDO::PARAM_STR);
    $stmt_insert->bindParam(":message",$currentMessage,PDO::PARAM_STR);
    $stmt_insert->bindParam(":senderID",$senderID,PDO::PARAM_STR);
    $stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);

    if ($stmt_insert->execute()) {
        $error   = false;
        $message = "";
        $id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("
            SELECT 
                a.imageLink 
            FROM 
                es_appaccount_ids a
            WHERE 
                a.userID = :userID 
            AND 
                a.isProfile = 1 
        ");
        $stmt->bindParam(":userID",$senderID,PDO::PARAM_STR);

        if ($stmt->execute()) {
            $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            foreach($rcrd AS $row) {
                $imageLink = $row["imageLink"];
            }
        }

    } else {
        $error   = true;
        $message = "Error sending message. Please try again later";
    }


	$response["error"]     = $error;
    $response["message"]   = $message;
    $response["imageLink"] = $imageLink;
    $response["id"]        = $id;
    
    echo json_encode($response);
?>