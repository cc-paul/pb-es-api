<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id = $_POST["id"];
	$countNotif = 0;
    $countMessage = 0;

	$sql = "SELECT * FROM es_notification WHERE isRead = 0 AND receiverID = $id AND IFNULL(message,'') != ''";
	$stmt = $pdo->prepare($sql);

   	if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            $countNotif++;
        }
    }


    $sql = "
        SELECT
            b.*
        FROM
            es_message_room a 
        INNER JOIN
            es_message_chat b 
        ON 
            a.chatID = b.chatID 
        WHERE
            a.senderID = $id
        OR 
            a.receiverID = $id 
    ";
    $stmt = $pdo->prepare($sql);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        
        foreach($rcrd AS $row) {
            if ($row["senderID"] != $id && $row["isRead"] == 0) {
                $countMessage++;
            }
        }
    }


    $response["countNotif"] = $countNotif;
    $response["countMessage"] = $countMessage;
    
    echo json_encode($response);
?>