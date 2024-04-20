<?php
	include 'conn.php';
    include 'justnow.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id       = $_POST["id"];
    $search   = $_POST["search"];
	$error    = false;
    $message  = "";
    $response = array();
    $result   = array();  

    $sql = "
        SELECT
            a.* 
        FROM (
            SELECT
                CONCAT(e.lastName,', ',e.firstName,' ',e.middleName) AS sendersName,
                CONCAT(f.lastName,', ',f.firstName,' ',f.middleName) AS receiversName,
                IFNULL(b.imageLink,'-') AS senderImageLink,
                IFNULL(c.imageLink,'-') AS receiverImageLink,(
                    SELECT 
                        message 
                    FROM 
                        es_message_chat 
                    WHERE
                        chatID = a.chatID 
                    ORDER BY
                        dateCreated DESC 
                    LIMIT 1
                ) AS lastMessage,(
                    SELECT 
                        dateCreated 
                    FROM 
                        es_message_chat 
                    WHERE
                        chatID = a.chatID 
                    ORDER BY
                        dateCreated DESC 
                    LIMIT 1
                ) AS lastDate,
                a.chatID,
                a.senderID,
                (
                    SELECT 
                        COUNT(1) 
                    FROM 
                        es_message_chat 
                    WHERE
                        chatID = a.chatID 
                    AND 
                        isRead = 0 
                    AND 
                        senderID != $id
                ) AS countUnread
            FROM
                es_message_room a 
            LEFT JOIN
                es_appaccount_ids b
            ON 
                a.senderID = b.userID 
            AND 
                b.isProfile = 1
            LEFT JOIN
                es_appaccount_ids c
            ON 
                a.receiverID = c.userID 
            AND 
                c.isProfile = 1
            INNER JOIN
                es_message_chat d 
            ON  
                a.chatID = d.chatID 
            INNER JOIN
                es_appaccount_registration e 
            ON 
                a.senderID = e.id
            INNER JOIN
                es_appaccount_registration f 
            ON 
                a.receiverID = f.id 
            WHERE 
                a.senderID = $id 
            OR 
                a.receiverID = $id
            GROUP BY
                a.chatID
        ) a 
        WHERE 
            a.sendersName LIKE '%".$search."%' 
        OR 
            a.receiversName LIKE '%".$search."%'
        ORDER BY
            a.lastDate DESC;
    ";

    $stmt = $pdo->prepare($sql);
   	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $imageLink = "";
            $name      = "";

            if ($row["senderID"] == $id) {
                $imageLink = $row["receiverImageLink"];
                $name      = $row["receiversName"];
            } else {
                $imageLink = $row["senderImageLink"];
                $name      = $row["sendersName"];
            }

            $temp["imageLink"]   = $imageLink;    
            $temp["name"]        = $name;
            $temp["lastMessage"] = $row["lastMessage"];    
            $temp["lastDate"]    = time_ago_in_php($row["lastDate"]);    
            $temp["chatID"]      = $row["chatID"]; 
            $temp["countUnread"] = $row["countUnread"];        
            
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
    $response["sql"]     = $sql; 
    
    echo json_encode($response);
?>