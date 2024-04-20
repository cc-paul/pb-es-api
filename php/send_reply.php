<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id             = $_POST["id"];
	$reply          = $_POST["reply"];

	$error    = false;
	$message  = "Reply has been posted successfully";
    $replyName = "";
    $replyImageLink = "";
	$response = array();
	$result   = array();  

	$stmt_insert = $pdo->prepare("UPDATE es_messages SET reply = :reply WHERE id = :id");
    $stmt_insert->bindParam(":reply",$reply,PDO::PARAM_STR);
    $stmt_insert->bindParam(":id",$id,PDO::PARAM_STR);
    


    if ($stmt_insert->execute()) {
        
        $sql = "
            SELECT
                CONCAT(b.firstName,' ',b.middleName,' ',b.lastName) AS fullName,
                IFNULL(c.imageLink,'-') AS imageLink
            FROM
                es_messages a 
            INNER JOIN
                es_appaccount_registration b 
            ON 
                a.feedbackToID = b.id
            LEFT JOIN 
                es_appaccount_ids c 
            ON  
                b.id = c.userID AND c.isProfile = 1 
            WHERE
                a.id = $id
        ";

        $stmt = $pdo->prepare($sql);


        if ($stmt->execute()) {
            $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            
            foreach($rcrd AS $row) {
                $replyName      = $row["fullName"]; 
                $replyImageLink = $row["imageLink"];
            }
        }

    } else {
        $error   = true;
        $message = "Error posting reply";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["replyName"] = $replyName;
    $response["replyImageLink"] = $replyImageLink;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>