<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$otp      = $_POST["otp"];
	$password = $_POST["password"];
	$email    = $_POST["email"];
	$isUsed   = 1;

	$error    = false;
	$message  = "Password has been changed successfully";
	$response = array();
	$result   = array();  

	$stmt_insert = $pdo->prepare("UPDATE es_appaccount_registration SET `password` = MD5(:password) WHERE emailAddress = :email");
    $stmt_insert->bindParam(":password",$password,PDO::PARAM_STR);
    $stmt_insert->bindParam(":email",$email,PDO::PARAM_STR);


    if ($stmt_insert->execute()) {
        $error   = false;
        $message = "Password has been changed";
    } else {
        $error   = true;
        $message = "Error changing password";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result; 
    
    echo json_encode($response);
?>