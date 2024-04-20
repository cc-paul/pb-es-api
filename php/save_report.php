<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$reportedID      = $_POST["reportedID"];
    $reportedByID    = $_POST["reportedByID"];
    $title           = $_POST["title"];
    $content         = $_POST["content"];
    $imageLinks      = $_POST["imageLinks"];


	$error    = false;
	$message  = "Report has been submitted successfully. We will carefully analyze your concern and provide necessary actions imidiately";
	$response = array();
	$result   = array();  

	$stmt_insert = $pdo->prepare("
        INSERT INTO es_cs_reports 
            (reportedID,reportedByID,title,content,imageLinks,dateCreated) 
        VALUES 
            (:reportedID,:reportedByID,:title,:content,:imageLinks,:dateCreated) 
    ");
    $stmt_insert->bindParam(":reportedID",$reportedID,PDO::PARAM_STR);
    $stmt_insert->bindParam(":reportedByID",$reportedByID,PDO::PARAM_STR);
    $stmt_insert->bindParam(":title",$title,PDO::PARAM_STR);
    $stmt_insert->bindParam(":content",$content,PDO::PARAM_STR);
    $stmt_insert->bindParam(":imageLinks",$imageLinks,PDO::PARAM_STR);
    $stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);


    if ($stmt_insert->execute()) {
        $error   = false;
    } else {
        $error   = true;
        $message = "Error sending report. Please try again later";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>