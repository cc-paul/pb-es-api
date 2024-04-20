<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$monthNumber  = $_POST["monthNumber"];
	$yearNumber   = $_POST["yearNumber"];
	$createdBy    = $_POST["createdBy"];
    $arrOtherDays = explode(',', $_POST["otherDays"]);
	$error        = false;
    $message      = "";
    $response     = array();
    $result       = array();  

    $sql = "
        SELECT
			DATE_FORMAT(a.scheduleFrom,'%e') AS day,
			DATE_FORMAT(a.scheduleFrom,'%h:%i %p') AS scheduleFrom,
			DATE_FORMAT(a.scheduleTo,'%h:%i %p') AS scheduleTo
		FROM
			es_schedule a
		WHERE
			DATE_FORMAT(a.scheduleFrom,'%c') = $monthNumber
		AND 
			YEAR(a.scheduleFrom) = $yearNumber 
		AND
			a.isActive = 1
		AND
			a.createdBy = $createdBy
		ORDER BY
			a.scheduleFrom ASC;
    ";

    foreach ($arrOtherDays as $days) {
        if ($days != "") {
            $temp = array();
                
            $temp["day"]          = $days;    
            $temp["scheduleFrom"] = "";
            $temp["scheduleTo"]   = "";
            $temp["doNotAdd"]     = 1;
            
            array_push($result, $temp); 
        } 
    }


    $stmt = $pdo->prepare($sql);
   	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["day"]          = $row["day"];    
            $temp["scheduleFrom"] = $row["scheduleFrom"];
            $temp["scheduleTo"]   = $row["scheduleTo"];
            $temp["doNotAdd"]     = 0;
            
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