<?php
	include 'conn.php';
    include 'justnow.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id       = $_POST["id"];
	$myID     = $_POST["myID"];
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array();  

    $sql = "
         SELECT
			a.id,
			CONCAT(b.lastName,', ',b.firstName,' ',b.middleName) AS fullName,
			'' AS replyName,
			a.`comment` AS feedback,
			a.dateCreated,
			IFNULL(c.imageLink,'-') AS imageLink,
			'-' AS replyImageLink,
			IF(a.createdBy = $myID,1,0) AS isYours,
			0 AS ableToReply,
			'' AS reply,
			REPLACE(FORMAT(a.bidAmount,2),'.00','') AS bidAmount,
			b.id AS userID
		FROM
			es_customer_post_comment a
		INNER JOIN
			es_appaccount_registration b 
		ON
			a.createdBy = b.id
		LEFT JOIN
			es_appaccount_ids c 
		ON
			b.id = c.userID
		AND 
			c.isProfile = 1
		WHERE
			a.isRemoved = 0 
		AND 
			a.postID = $id
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
            $temp["fullName"]    = $row["fullName"];
            $temp["feedback"]    = $row["feedback"];
            $temp["dateCreated"] = time_ago_in_php($row["dateCreated"]);   
            $temp["imageLink"]   = $row["imageLink"];
            $temp["isYours"]     = $row["isYours"];
            $temp["ableToReply"] = $row["ableToReply"];
            $temp["replyName"] = $row["replyName"];
            $temp["replyImageLink"] = $row["replyImageLink"];
            $temp["reply"]      = $row["reply"];
            $temp["bidAmount"] = $row["bidAmount"];
            $temp["userID"] = $row["userID"];
            
            
            array_push($result, $temp);
        }
    } else {
		$merror= $stmt->errorInfo();

        $error = true;
        $message = "Error getting comments".$merror[2];
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>