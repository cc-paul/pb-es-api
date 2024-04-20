<?php
	include 'conn.php';
    include 'justnow.php';
	$pdo = new PDO($dsn, $user, $passwd);

    $search   = $_POST["search"]; 
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array();  

    $sql = "
        SELECT
            h.id,
            IFNULL(g.imageLink,'-') AS imageLink,
            a.mobileNumber,
            CONCAT(a.streetName,' ',d.barangayName,' ',c.municipalityName,' (',c.zipCode,') ',b.province) AS address,
            h.content,
            h.dateCreated,
            h.imageLinks,
            CONCAT(a.lastName,', ',a.firstName,' ',a.middleName) AS fullName,
            a.id AS userID,(
                SELECT COUNT(1) FROM es_customer_post_comment WHERE postID = h.id AND isRemoved = 0
            ) AS countComment,
            a.emailAddress AS email
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
        INNER JOIN
            es_customer_post h 
        ON
            h.createdBy = a.id 
        WHERE 
            CONCAT(a.lastName,', ',a.firstName,' ',a.middleName) LIKE '%".$search."%'
        OR 
            h.content LIKE '%".$search."%' 
        GROUP BY 
            h.id 
        ORDER BY 
            h.dateCreated DESC;
    ";

    $stmt = $pdo->prepare($sql);
   	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["id"]           = $row["id"];    
            $temp["imageLink"]    = $row["imageLink"];
            $temp["mobileNumber"] = $row["mobileNumber"];
            $temp["address"]      = $row["address"];
            $temp["content"]      = $row["content"];
            $temp["dateCreated"]  = time_ago_in_php($row["dateCreated"]);
            $temp["imageLinks"]   = $row["imageLinks"];
            $temp["fullName"]     = $row["fullName"];
            $temp["userID"]       = $row["userID"];
            $temp["countComment"] = $row["countComment"];
            $temp["email"]        = $row["email"];

            array_push($result, $temp);
        }
    } else {
		$merror= $stmt->errorInfo();

        $error = true;
        $message = "Error getting profile".$merror[2];
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result;
    
    echo json_encode($response);
?>