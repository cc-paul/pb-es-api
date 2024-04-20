<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$addressType = $_POST["addressType"];
	$id          = $_POST["id"];

	$error    = false;
  $message  = "";
  $response = array();
  $result   = array(); 
  $sql      = "";


   	switch ($addressType) {
   		case 'province':

   				$sql = "
   					SELECT
						a.id,
						a.province AS label
					FROM
						es_province a
					WHERE
						a.isActive = 1
					ORDER BY
						a.province ASC;
   				";

   			break;
   		
   		case 'municipality':

   				$sql = "
   					SELECT
						a.id,
						CONCAT(a.municipalityName,' (',a.zipCode,')') AS label
					FROM
						es_municipality a
					WHERE
						a.isActive = 1
					AND 
						a.provinceID = $id
					ORDER BY
						a.municipalityName ASC;
   				";

   			break;

   		case 'barangay':

   				$sql = "
   					SELECT
						a.id,
						a.barangayName AS label
					FROM
						es_barangay a
					WHERE
						a.isActive = 1
					AND 
						a.municipalityID = $id
					ORDER BY
						a.barangayName ASC;
   				";

   			break;
   	}

   	$stmt = $pdo->prepare($sql);
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