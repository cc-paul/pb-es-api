<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id        = $_POST["id"];
	$error     = false;
    $response  = array();
    $result    = array();  

    $stmt = $pdo->prepare("
        SELECT
            a.id,
            CONCAT(a.lastName,', ',a.firstName,' ',a.middleName) AS fullName,
            a.emailAddress,
            func_proper(a.gender) AS gender,
            DATE_FORMAT(a.birthDate, '%M %D, %Y') AS birthDate,
            CONCAT(a.streetName,' ',d.barangayName,' ',c.municipalityName,' (',c.zipCode,') ',b.province) AS address,
            IFNULL(GROUP_CONCAT(f.id),0) AS serviceIDs,
            IFNULL(GROUP_CONCAT(f.service),'') AS services,
            a.provinceID,
            a.municipalityID,
            a.barangayID,
            a.mobileNumber,
            IFNULL(g.imageLink,0) AS imageLink,
            a.streetName,
            d.barangayName,
            CONCAT(c.municipalityName,' (',c.zipCode,')') AS municipalityName,
            b.province
        FROM
            es_appaccount_registration a
        INNER JOIN
            es_province b
        ON
            a.provinceID = b.id
        INNER JOIN
            es_municipality c
        ON
            a.municipalityID = c.id
        INNER JOIN
            es_barangay d
        ON
            a.barangayID = d.id
        LEFT JOIN
            es_appaccount_services e
        ON
            a.id = e.userID
        LEFT JOIN
            es_service f
        ON
            e.serviceID = f.id
        LEFT JOIN
            es_appaccount_ids g
        ON 
            a.id = g.userID AND g.isProfile = 1
        WHERE 
            a.id = :id
        GROUP BY
            a.id
    ");

    $stmt->bindParam(":id",$id,PDO::PARAM_STR);


    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["id"]             = $row["id"]; 
            $temp["fullName"]       = $row["fullName"]; 
            $temp["emailAddress"]   = $row["emailAddress"]; 
            $temp["gender"]         = $row["gender"]; 
            $temp["birthDate"]      = $row["birthDate"]; 
            $temp["address"]        = $row["address"]; 
            $temp["serviceIDs"]     = $row["serviceIDs"]; 
            $temp["services"]       = $row["services"]; 
            $temp["provinceID"]     = $row["provinceID"]; 
            $temp["municipalityID"] = $row["municipalityID"]; 
            $temp["barangayID"]     = $row["barangayID"]; 
            $temp["mobileNumber"]   = $row["mobileNumber"]; 
            $temp["imageLink"]      = $row["imageLink"]; 
            $temp["streetName"]       = $row["streetName"]; 
            $temp["barangayName"]     = $row["barangayName"]; 
            $temp["municipalityName"] = $row["municipalityName"]; 
            $temp["province"]         = $row["province"]; 
            $temp["images"]           = getImages($row["id"]);

            
            array_push($result, $temp);
            $count++;
        }
        
        if ($count == 0) {
            $error   = true;
        }
    } else {
        $error = true;
    }

    function getImages($userID) {
        global $pdo;
        
        $items  = array();
        $output = array();

        $stmt = $pdo->prepare("SELECT id,imageLink FROM es_appaccount_ids WHERE userID=:id AND imageLink != '~~~' AND isDeleted = 0");
        $stmt->bindParam(":id",$userID,PDO::PARAM_STR);

        if ($stmt->execute()) {
            $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            $items = array();

            foreach($rcrd AS $row) {
                $id        = $row["id"];
                $imageLink = $row["imageLink"];

                $temp = array();
                $temp["id"]        = $id;
                $temp["imageLink"] = $imageLink;
                array_push($items, $temp);
            }
            
            return $items;
        } else {
            $error = true;
            $message = "error getting images";
        }
    }


    $response["error"]   = $error;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>