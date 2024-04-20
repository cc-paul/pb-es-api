<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

    $id           = $_POST["id"];
    $isFreelancer = $_POST["isFreelancer"];

	$error        = false;
    $message      = "";
    $response     = array();
    $result       = array();  

    $sql = "
        SELECT
            a.id AS bidID,
            b.id AS taskID,
            
            c.id AS freelancerID,
            IFNULL(d.imageLink,'-') AS imageLink,
            CONCAT(c.lastName,', ',c.firstName,' ',c.middleName) AS freelancerName,
            CONCAT(c.streetName,' ',g.barangayName,' ',f.municipalityName,' (',f.zipCode,') ',e.province) AS address,
            c.mobileNumber,
            c.emailAddress,
            
            h.id AS customerID,
            IFNULL(i.imageLink,'-') AS customerImageLink,
            CONCAT(h.lastName,', ',h.firstName,' ',h.middleName) AS customerName,
            a.address AS customerAddress,
            h.mobileNumber AS customerMobileNumber,
            h.emailAddress AS customerEmailAddress,
            
            IF(IFNULL(b.title,'') = '','No Title Indicated',b.title) AS title,
            a.address AS venue,
            DATE_FORMAT(b.scheduleFrom, '%m/%d/%Y') AS dateSched,
            CONCAT(DATE_FORMAT(b.scheduleFrom,'%h:%i %p'),' - ',DATE_FORMAT(b.scheduleTo,'%h:%i %p')) AS timeSched,
            CONCAT('P',REPLACE(FORMAT(a.bidAmount,2),'.00','')) AS bidAmount,
            IF(IFNULL(b.remarks,'') = '','No Remarks Indicated',b.remarks) AS remarks,
            IF(a.userID = $id AND b.isServiceDone = 0 AND b.scheduleTo < '$global_date',0,1) AS setToDisable,
            IF(a.userID = $id,1,0) AS isYours,
            (
                    SELECT
                            IF(COUNT(id) != 0,1,0)
                    FROM
                            es_likers
                    WHERE
                            likedByID = 1
                    AND 
                            likedID = b.createdBy
            ) AS isLiked,
            b.isServiceDone
        FROM
                es_bid a
        INNER JOIN
                es_schedule b
        ON
                a.taskID = b.id 
        AND 
                a.isAccepted = 1 
        INNER JOIN
                es_appaccount_registration c
        ON
                b.createdBy = c.id
        LEFT JOIN
                es_appaccount_ids d
        ON 
                c.id = d.userID AND d.isProfile = 1
        INNER JOIN
                es_province e
        ON
                c.provinceID = e.id
        INNER JOIN
                es_municipality f
        ON
                c.municipalityID = f.id
        INNER JOIN 
            es_barangay g 
        ON 
            c.provinceID = g.provinceID AND c.municipalityID = g.municipalityID AND c.barangayID = g.id AND g.isActive = 1
        INNER JOIN 
            es_appaccount_registration h
        ON 
            a.userID = h.id 
        LEFT JOIN
            es_appaccount_ids i 
        ON 
            h.id = i.userID AND i.isProfile = 1
        WHERE
            a.isAccepted = 1
        AND 
            a.userID = $id
        OR 
            b.createdBy = $id
        ORDER BY
            b.dateCreated DESC;
    ";


    $stmt = $pdo->prepare($sql);
   	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["bidID"]          = $row["bidID"];    
            $temp["taskID"]         = $row["taskID"];    
            $temp["imageLink"]      = $row["imageLink"];    
            $temp["title"]          = $row["title"];    
            $temp["venue"]          = $row["venue"];    
            $temp["dateSched"]      = $row["dateSched"];    
            $temp["timeSched"]      = $row["timeSched"];    
            $temp["bidAmount"]      = $row["bidAmount"];    
            $temp["remarks"]        = $row["remarks"];
            $temp["setToDisable"]   = $row["setToDisable"];
            $temp["isYours"]        = $row["isYours"];
            $temp["isLiked"]        = $row["isLiked"];
            $temp["isServiceDone"]  = $row["isServiceDone"];

            $temp["freelancerID"]   = $row["freelancerID"];
            $temp["imageLink"]      = $row["imageLink"];
            $temp["freelancerName"] = $row["freelancerName"];   
            $temp["address"]        = $row["address"]; 
            $temp["mobileNumber"]   = $row["mobileNumber"]; 
            $temp["emailAddress"]   = $row["emailAddress"];  
            
            $temp["customerID"]           = $row["customerID"];
            $temp["customerImageLink"]    = $row["customerImageLink"];
            $temp["customerName"]         = $row["customerName"];   
            $temp["customerAddress"]      = $row["customerAddress"]; 
            $temp["customerMobileNumber"] = $row["customerMobileNumber"]; 
            $temp["customerEmailAddress"] = $row["customerEmailAddress"];  

            
            array_push($result, $temp);
        }
    } else {
		$merror  = $stmt->errorInfo();

        $error   = true;
        $message = "Error getting reservation : ". $merror[2];
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>