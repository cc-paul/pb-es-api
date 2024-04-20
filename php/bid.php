<?php
    include 'conn.php';
    $pdo = new PDO($dsn, $user, $passwd);

    $taskID       = $_POST["taskID"];
    $userID       = $_POST["userID"];
    $bidAmount    = $_POST["bidAmount"];
    $address      = $_POST["address"];
    $notifMessage = $_POST["message"];


    $error    = false;
    $message  = "Book has been saved successfully";
    $response = array();
    $result   = array();  


    $stmt = $pdo->prepare("SELECT * FROM es_bid WHERE taskID = :taskID AND isAccepted = 1");
    $stmt->bindParam(":taskID",$taskID,PDO::PARAM_STR);

    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;
        
        foreach($rcrd AS $row) {
            $count++;
        }
        
        if ($count == 0) {
            $stmt_insert = $pdo->prepare("UPDATE es_bid SET isActive = 0 WHERE taskID=:taskID AND userID=:userID");
            $stmt_insert->bindParam(":taskID",$taskID,PDO::PARAM_STR);
            $stmt_insert->bindParam(":userID",$userID,PDO::PARAM_STR);

            if ($stmt_insert->execute()) {
                $stmt_insert = $pdo->prepare("INSERT INTO es_bid (taskID,userID,bidAmount,address,isAccepted) VALUES (:taskID,:userID,:bidAmount,:address,1)");
                $stmt_insert->bindParam(":taskID",$taskID,PDO::PARAM_STR);
                $stmt_insert->bindParam(":userID",$userID,PDO::PARAM_STR);
                $stmt_insert->bindParam(":bidAmount",$bidAmount,PDO::PARAM_STR);
                $stmt_insert->bindParam(":address",$address,PDO::PARAM_STR);

                if ($stmt_insert->execute()) {
                    $stmt_insert = $pdo->prepare("
                        INSERT INTO es_notification (
                            message,
                            receiverID,
                            dateCreated,
                            taskID
                        )
                        SELECT
                            :message,
                            createdBy,
                            :dateCreated,
                            :taskID
                        FROM
                            es_schedule
                        WHERE 
                            id = :currentTaskID
                    ");
                    $stmt_insert->bindParam(":message",$notifMessage,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":taskID",$taskID,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":currentTaskID",$taskID,PDO::PARAM_STR);

                    if ($stmt_insert->execute()) {
                        $error   = false;
                        $message = "Book has been saved successfully";
                    } else {
                        $error   = true;
                        $message = "Error saving notification but the book has been accepted";
                    }
                } else {
                    $error   = true;
                    $message = "Error saving book";
                }
            } else {
                $error   = true;
                $message = "Error removing book";
            }
        } else {
            $error   = true;
            $message = "Schedule is already closed";
        }
    } else {
        $error = true;
        $message = "Error checking account";
    }


    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>