<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$chatID   = $_POST["chatID"];
    $senderID = $_POST["senderID"];
    $isAll    = $_POST["isAll"];
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array();  
    $sql      = "";

    $stmt = $pdo->prepare("
        SELECT 
            a.id,
            IFNULL(b.imageLink,'-') AS imageLink,
            a.message,
            a.senderID
        FROM
            es_message_chat a
        LEFT JOIN
            es_appaccount_ids b
        ON 
            a.senderID = b.userID 
        AND 
            b.isProfile = 1 
        WHERE 
            a.chatID = :chatID
        ORDER BY
            a.dateCreated ASC;
    ");
    $stmt->bindParam(":chatID",$chatID,PDO::PARAM_STR);


    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["id"]        = $row["id"];  
            $temp["imageLink"] = $row["imageLink"];    
            $temp["message"]   = $row["message"];
            $temp["senderID"]  = $row["senderID"];            

            if ($row["senderID"] != $senderID) {
                $stmt_insert = $pdo->prepare("UPDATE es_message_chat SET isRead = 1 WHERE id = :id");
                $stmt_insert->bindParam(":id",$row["id"],PDO::PARAM_STR);
                $stmt_insert->execute();
            }

            array_push($result, $temp);
        }
    } else {
        $error = true;
        $message = "Unable to retreive message. Please try again later.";
    }


    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    $response["sql"]     = "
        SELECT 
            a.id,
            IFNULL(b.imageLink,'-') AS imageLink,
            a.message,
            a.senderID
        FROM
            es_message_chat a
        LEFT JOIN
            es_appaccount_ids b
        ON 
            a.senderID = b.userID 
        AND 
            b.isProfile = 1 
        WHERE 
            a.chatID = $chatID
        ORDER BY
            a.dateCreated ASC;
    "; 
    
    echo json_encode($response);
?>