<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

    $search   = $_POST["search"]; 
    $id       = $_POST["id"]; 
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array();  

    $sql = "
        SELECT a.* FROM (
            SELECT
                CONCAT(a.lastName,', ',a.firstName,' ',a.middleName) AS fullName,
                a.mobileNumber,
                CONCAT(a.streetName,' ',d.barangayName,' ',c.municipalityName,' (',c.zipCode,') ',b.province) AS fullAddress,
                proper(a.gender) AS gender,
                DATE_FORMAT(a.birthDate,'%m/%d/%Y') AS fBdate,
                a.birthDate AS nfBdate,
                a.username,
                a.emailAddress,(
                    SELECT
                            GROUP_CONCAT(g.service SEPARATOR '~') AS services
                    FROM
                            es_service g
                    INNER JOIN
                            es_appaccount_services h
                    ON
                            g.id = h.serviceID
                    WHERE
                            h.userID = a.id
                ) AS services,(
                    SELECT
                            COUNT(1) AS services
                    FROM
                            es_service g
                    INNER JOIN
                            es_appaccount_services h
                    ON
                            g.id = h.serviceID
                    WHERE
                            h.userID = a.id
                ) AS servicesCount,IFNULL((
                        SELECT imageLink FROM es_appaccount_ids WHERE isProfile = 1 AND userID = a.id
                ),'-') AS imageLink,
                a.id,
                IFNULL(e.totalLike,0) AS countLikers,
                IFNULL(f.totalMessage,0) AS countMessages,
                IFNULL((
                    SELECT 
                        COUNT(id)
                    FROM
                        es_schedule
                    WHERE
                        createdBy = a.id
                    AND
                        DATE(scheduleFrom) = DATE('".$global_date."')
                ),0) AS countServices,
                (
                    SELECT
                        IF(COUNT(id) != 0,1,0)
                    FROM
                        es_likers
                    WHERE
                        likedByID = ".$id."
                    AND 
                        likedID = a.id
                ) AS isLiked,
                IF(IFNULL(g.customerID,0) != 0,1,0) AS isServiceMade
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
                (
                    SELECT
                        likedID,
                        COUNT(*) AS totalLike
                    FROM
                        es_likers
                    GROUP BY
                        likedID
                ) e
            ON
                a.id = e.likedID 
            LEFT JOIN
                (
                    SELECT
                        feedbackToID,
                        COUNT(*) AS totalMessage
                    FROM
                        es_messages
                    WHERE 
                        isRemoved = 0 
                    GROUP BY
                        feedbackToID
                ) f
            ON
                a.id = f.feedbackToID
            LEFT JOIN (
                SELECT
                    a.createdBy AS freelancerID,
                    b.userID AS customerID 
                FROM
                    es_schedule a
                INNER JOIN
                    es_bid b 
                ON
                    a.id = b.taskID
                WHERE
                    a.isServiceDone = 1
                AND
                    a.isActive = 1
                AND 
                    b.userID = ".$id."
            ) g
            ON
                a.id = g.freelancerID
            WHERE
                a.isActive = 1
            AND
                a.isRegularUser = 0
            AND
                a.`status` != 'Banned' 
            AND 
                a.`status` != 'Cancelled'
            GROUP BY
                    a.id
        ) a
        WHERE
            a.fullName LIKE '%".$search."%'
        OR 
            a.fullAddress LIKE '%".$search."%'
        OR 
            a.services LIKE '%".$search."%' 
        ORDER BY 
            a.countServices DESC;
    ";

    $stmt = $pdo->prepare($sql);
   	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["fullName"]      = $row["fullName"];    
            $temp["mobileNumber"]  = $row["mobileNumber"];
            $temp["fullAddress"]   = $row["fullAddress"];
            $temp["gender"]        = $row["gender"];
            $temp["fBdate"]        = $row["fBdate"];
            $temp["nfBdate"]       = $row["nfBdate"];
            $temp["username"]      = $row["username"];
            $temp["services"]      = $row["services"];
            $temp["servicesCount"] = $row["servicesCount"];
            $temp["imageLink"]     = $row["imageLink"];
            $temp["id"]            = $row["id"];
            $temp["countLikers"]   = $row["countLikers"];
            $temp["countMessages"] = $row["countMessages"];
            $temp["countServices"] = $row["countServices"];
            $temp["isLiked"]       = $row["isLiked"];
            $temp["isServiceMade"] = $row["isServiceMade"];

            if ($search == "") {
                if ($temp["countServices"] != 0) {
                    array_push($result, $temp);
                }
            } else {
                array_push($result, $temp);
            }
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