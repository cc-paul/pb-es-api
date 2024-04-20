<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$taskID       = $_POST["taskID"];
	$error        = false;
    $message      = "";
    $response     = array();
    $result       = array();  

    $stmt = $pdo->prepare("
        SELECT
            a.taskID,
            a.userID,
            REPLACE(FORMAT(a.bidAmount,2),'.00','') AS bidAmount,
            a.address,
            a.isAccepted,
            a.id
        FROM
            es_bid a
        WHERE
            a.isActive = 1
        AND
            a.taskID = :taskID
    ");

    $stmt->bindParam(":taskID",$taskID,PDO::PARAM_STR);


    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["taskID"]     = $row["taskID"];  
            $temp["userID"]     = $row["userID"];    
            $temp["bidAmount"]  = $row["bidAmount"];
            $temp["address"]    = $row["address"];
            $temp["isAccepted"] = $row["isAccepted"];
            $temp["id"]         = $row["id"];
            
            array_push($result, $temp);
            $count++;
        }
    } else {
        $error = true;
        $message = "Error checking  bid";
    }


    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>