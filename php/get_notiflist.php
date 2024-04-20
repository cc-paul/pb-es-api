<?php
	include 'conn.php';
    include 'justnow.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id       = $_POST["createdBy"];
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array();  

    $sql = "
        SELECT
            a.*
        FROM
            es_notification a WHERE  IFNULL(a.message,'') != '' AND a.receiverID = $id
        ORDER BY
            a.dateCreated DESC
    ";

    $stmt = $pdo->prepare($sql);
   	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["id"]          = $row["id"];    
            $temp["message"]     = $row["message"];
            $temp["receiverID"]  = $row["receiverID"];
            $temp["dateCreated"] = time_ago_in_php($row["dateCreated"]);    
            $temp["taskID"]      = $row["taskID"];
            $temp["isRead"]      = $row["isRead"];

            $stmt_insert = $pdo->prepare("UPDATE es_notification SET isRead = 1 WHERE id=:id");
            $stmt_insert->bindParam(":id",$row["id"],PDO::PARAM_STR);
            $stmt_insert->execute();
            
            array_push($result, $temp);
        }
    } else {
		$merror= $stmt->errorInfo();

        $error = true;
        $message = "Error getting schedules".$merror[2];
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>