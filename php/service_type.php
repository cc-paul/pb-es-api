<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$addressType = $_POST["addressType"];
	$id          = $_POST["id"];

	$error    = false;
    $message  = "";
    $response = array();
    $result   = array(); 


    $stmt = $pdo->prepare("
    	SELECT
			a.id,
			a.service AS label
		FROM
			es_service a
		WHERE
			a.isActive = 1
		ORDER BY
			a.service ASC;
    ");
   	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["id"]    = $row["id"];    
            $temp["label"] = $row["label"];
            
            array_push($result, $temp);
        }
    } else {
        $error = true;
        $message = "Error getting address type";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>