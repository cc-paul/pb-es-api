<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$likeID           = $_POST["likeID"];
	$likedByID        = $_POST["likedByID"];
	$isLiked          = $_POST["isLiked"];

	$response     = array();
    $error        = false;
    $message      = "";
    $newTotalLike = 0;
    $query        = "";


    if ($isLiked == 1) {
    	$query = "INSERT INTO es_likers (likedID,likedByID) VALUES (:likeID,:likedByID)";
    } else {
    	$query = "DELETE FROM es_likers WHERE likedID=:likeID AND likedByID=:likedByID";
    }


    $stmt_insert = $pdo->prepare($query);
	$stmt_insert->bindParam(":likeID",$likeID,PDO::PARAM_STR);
	$stmt_insert->bindParam(":likedByID",$likedByID,PDO::PARAM_STR);

	if ($stmt_insert->execute()) {
	   	
		$sql = "SELECT * FROM es_likers WHERE likedID = " . $likeID;
	    $stmt = $pdo->prepare($sql);
	   	if ($stmt->execute()) {
	        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
	        $stmt->closeCursor();
	        
	        foreach($rcrd AS $row) {
	            $newTotalLike++;
	        }
	    } else {
			$merror= $stmt->errorInfo();

	        $error = true;
	        $message = "Error getting likes : ".$merror[2];
	    }

	} else {
	    $error        = true;
	    $message      = "Unable to send like";
	}

    $response["error"]        = $error;
    $response["message"]      = $message;
    $response["newTotalLike"] = $newTotalLike;
    
    echo json_encode($response);
?>