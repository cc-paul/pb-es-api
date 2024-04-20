<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$email = $_POST["email"];
	$otp   = rand(100000, 999999);

	$response = array();
    $error    = false;
    $message  = "";
    $query    = "";


    $stmt_insert = $pdo->prepare("INSERT INTO es_otp (otp,email) VALUES (:otp,:email)");
	$stmt_insert->bindParam(":otp",$otp,PDO::PARAM_STR);
	$stmt_insert->bindParam(":email",$email,PDO::PARAM_STR);

	if ($stmt_insert->execute()) {
	   	$message = "Here is your OTP for changing password " . $otp;

	} else {
	    $error   = true;
	    $message = "Error getting OTP. Please try again later";
	}

	$stmt_insert = $pdo->prepare("INSERT INTO es_otp (otp,email) VALUES (:otp,:email)");
	$stmt_insert->bindParam(":otp",$otp,PDO::PARAM_STR);
	$stmt_insert->bindParam(":email",$email,PDO::PARAM_STR);



	$response["error"]   = $error;
    $response["message"] = $message;
    
    echo json_encode($response);
?>