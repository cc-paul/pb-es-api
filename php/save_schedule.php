<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$title          = $_POST["title"];
	$scheduleFrom   = $_POST["scheduleFrom"];
	$scheduleTo     = $_POST["scheduleTo"];
    $o_scheduleFrom = $_POST["o_scheduleFrom"];
    $o_scheduleTo   = $_POST["o_scheduleTo"];
	$remarks        = $_POST["remarks"];
	$createdBy      = $_POST["createdBy"];
	$rate           = $_POST["rate"];
    $id             = $_POST["id"];

	$error    = false;
    $message  = "";
    $response = array();
    $result   = array();  


    if ($id == 0) {
        if (!checkIfExisting()) {
            $stmt_insert = $pdo->prepare("
                INSERT INTO es_schedule (title,scheduleFrom,scheduleTo,remarks,dateCreated,createdBy,rate) VALUES (:title,:scheduleFrom,:scheduleTo,:remarks,:dateCreated,:createdBy,:rate);
            ");
            $stmt_insert->bindParam(":title",$title,PDO::PARAM_STR);
            $stmt_insert->bindParam(":scheduleFrom",$scheduleFrom,PDO::PARAM_STR);
            $stmt_insert->bindParam(":scheduleTo",$scheduleTo,PDO::PARAM_STR);
            $stmt_insert->bindParam(":remarks",$remarks,PDO::PARAM_STR);
            $stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);
            $stmt_insert->bindParam(":createdBy",$createdBy,PDO::PARAM_STR);
            $stmt_insert->bindParam(":rate",$rate,PDO::PARAM_STR);

            if ($stmt_insert->execute()) {
                $error   = false;
                $message = "Schedule has been saved successfully";
            } else {
                $error   = true;
                $message = "Error saving your schedule";
            }
        } else {
            $error   = true;
            $message = "Schedule already exist or conflict with others";
        }
    } else {
        $isScheduleExist = checkIfExisting();

        if ($isScheduleExist) {
            $error   = true;
            $message = "Schedule already exist or conflict with others";
        } else {
            $stmt_insert = $pdo->prepare("
                UPDATE es_schedule SET title=:title,scheduleFrom=:scheduleFrom,scheduleTo=:scheduleTo,remarks=:remarks,rate=:rate WHERE id=:id
            ");
            $stmt_insert->bindParam(":title",$title,PDO::PARAM_STR);
            $stmt_insert->bindParam(":scheduleFrom",$scheduleFrom,PDO::PARAM_STR);
            $stmt_insert->bindParam(":scheduleTo",$scheduleTo,PDO::PARAM_STR);
            $stmt_insert->bindParam(":remarks",$remarks,PDO::PARAM_STR);
            $stmt_insert->bindParam(":rate",$rate,PDO::PARAM_STR);
            $stmt_insert->bindParam(":id",$id,PDO::PARAM_STR);

            if ($stmt_insert->execute()) {
                $error   = false;
                $message = "Schedule has been saved successfully";
            } else {
                $error   = true;
                $message = "Error saving your schedule";
            }
        }
    }
    

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 

    echo json_encode($response);

    function checkIfExisting() {
        global $pdo,$scheduleFrom,$o_scheduleFrom,$scheduleTo,$createdBy,$id;
        $isDataExist   = false;
        $doTheChecking = false;

            
        if ($id == 0) {
            $doTheChecking = true;
        } else {
            if ($scheduleFrom != $o_scheduleFrom) {
                $doTheChecking = true;
            }
        }


        if ($doTheChecking) {
            $stmt = $pdo->prepare("
                SELECT 
                    * 
                FROM 
                    es_schedule 
                WHERE
                    scheduleFrom <= :scheduleFrom
                AND 
                    scheduleTo >= :scheduleFrom
                AND 
                    isActive = 1
                AND 
                    createdBy = :createdBy
            ");
            $stmt->bindParam(":scheduleFrom",$scheduleFrom,PDO::PARAM_STR);
            $stmt->bindParam(":createdBy",$createdBy,PDO::PARAM_STR);
            $stmt->bindParam(":id",$id,PDO::PARAM_STR);

            if ($stmt->execute()) {
                $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
                $stmt->closeCursor();
                $count = 0;
                
                foreach($rcrd AS $row) {
                    $count++;
                }

                $isDataExist = $count != 0 ? true : false;
            } else {
                $isDataExist = true;
            }
        }

        return $isDataExist;
    }
?>