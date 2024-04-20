<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$id         = $_POST["id"];
	$imageLinks = explode(',', $_POST["imageLinks"]); 

	$response = array();
    $error    = false;
    $message  = "";

    $queryImageLink = array();
    foreach($imageLinks as $imageLink) {
    	array_push($queryImageLink, "(".$id.",'".$imageLink."')");
	}

	
	$stmt_insert = $pdo->prepare('INSERT INTO es_appaccount_ids (userID,imageLink) VALUES ' . join(",",$queryImageLink));
	if ($stmt_insert->execute()) {
		$error   = false;
		$message = "Image has been uploaded";
	} else {
		$error   = true;
		$message = "Error uploading image";
	}

	$response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>