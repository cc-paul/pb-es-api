<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$createdBy    = $_POST["createdBy"];
	$currentDate  = $_POST["currentDate"];
	$error        = false;
    $message      = "";
    $response     = array();
    $result       = array();  

    $sql = "
        SELECT
            a.id,
            IF(IFNULL(a.title,'') = '','No Title Indicated',a.title) AS title,
            DATE_FORMAT(a.scheduleFrom,'%h:%i %p') AS scheduleFrom,
            DATE_FORMAT(a.scheduleTo,'%h:%i %p') AS scheduleTo,
            IF(IFNULL(a.remarks,'') = '','No Remarks Included',a.remarks) AS remarks,
            DATE_FORMAT(scheduleFrom, '%H:%i') AS s24HourFrom,
            DATE_FORMAT(scheduleTo, '%H:%i') AS s24HourTo,
            REPLACE(FORMAT(a.rate,2),'.00','') AS rate,IFNULL((
                SELECT 
                    REPLACE(FORMAT(bidAmount,2),'.00','') AS bidAmount
                FROM
                    es_bid
                WHERE
                    taskID = a.id
                AND
                    isActive = 1
                ORDER BY
                    bidAmount DESC
                LIMIT 1
            ),0) bidAmount,
            IF(a.scheduleFrom < '$global_date',1,0) AS isPass,
            IF((SELECT COUNT(1) FROM es_bid WHERE taskID = a.id) != 0,1,0) AS hasBid,IFNULL((
                SELECT 
                    isAccepted
                FROM
                    es_bid
                WHERE
                    taskID = a.id
                ORDER BY
                    bidAmount
                DESC
                    LIMIT 1
            ),0) AS isAccepted,
            b.emailAddress
        FROM
            es_schedule a
        INNER JOIN 
            es_appaccount_registration b 
        ON 
            a.createdBy = b.id
        WHERE
            a.isActive = 1
        AND 
            a.createdBy = $createdBy
        AND 
            DATE(a.scheduleFrom) = '$currentDate'
        ORDER BY 
            a.scheduleFrom ASC;
    ";

    $stmt = $pdo->prepare($sql);
   	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["id"]           = $row["id"];    
            $temp["title"]        = $row["title"];
            $temp["scheduleFrom"] = $row["scheduleFrom"];
            $temp["scheduleTo"]   = $row["scheduleTo"];    
            $temp["remarks"]      = $row["remarks"];
            $temp["s24HourFrom"]  = $row["s24HourFrom"];
            $temp["s24HourTo"]    = $row["s24HourTo"];
            $temp["rate"]         = $row["rate"];
            $temp["bidAmount"]    = $row["bidAmount"];
            $temp["isPass"]       = $row["isPass"];
            $temp["hasBid"]       = $row["hasBid"];
            $temp["isAccepted"]   = $row["isAccepted"];
            $temp["emailAddress"] = $row["emailAddress"];
            
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