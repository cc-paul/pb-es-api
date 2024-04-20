<?php
	include 'conn.php';
    include 'justnow.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id       = $_POST["id"];
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array();  

    $sql = "
        SELECT
			CONCAT(b.lastName,', ',b.firstName,' ',b.middleName) AS fullName,
			IFNULL(c.imageLink,'-') AS imageLink
		FROM
			es_likers a
		INNER JOIN
			es_appaccount_registration b 
		ON
			a.likedByID = b.id
		LEFT JOIN
			es_appaccount_ids c 
		ON
			b.id = c.userID
		AND 
			c.isProfile = 1
		WHERE
			a.likedID = $id
		ORDER BY 
			CONCAT(b.lastName,', ',b.firstName,' ',b.middleName) ASC
    ";

    $stmt = $pdo->prepare($sql);
   	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["fullName"]  = $row["fullName"]; 
            $temp["imageLink"] = $row["imageLink"];
            
            array_push($result, $temp);
        }
    } else {
		$merror= $stmt->errorInfo();

        $error = true;
        $message = "Error getting applaud".$merror[2];
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>