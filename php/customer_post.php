<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$content    = $_POST["content"];
	$createdBy  = $_POST["createdBy"];
	$imageLinks = $_POST["imageLinks"];


	$response = array();
    $error    = false;
    $message  = "";
    $query    = "";


    $stmt_insert = $pdo->prepare("INSERT INTO es_customer_post (content,createdBy,dateCreated,imageLinks) VALUES (:content,:createdBy,:dateCreated,:imageLinks)");
	$stmt_insert->bindParam(":content",$content,PDO::PARAM_STR);
	$stmt_insert->bindParam(":createdBy",$createdBy,PDO::PARAM_STR);
	$stmt_insert->bindParam(":dateCreated",$global_date,PDO::PARAM_STR);
	$stmt_insert->bindParam(":imageLinks",$imageLinks,PDO::PARAM_STR);

	if ($stmt_insert->execute()) {
		$error   = false;
	   	$message = "Post has been shared publicly";
	} else {
	    $error   = true;
	    $message = "Error posting publicly. Please try again later";
	}

	$response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>