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

    $stmt = $pdo->prepare("SELECT * FROM es_otp WHERE otp = :otp AND email = :email AND isUsed = 0 LIMIT 1");
    $stmt->bindParam(":otp",$otp,PDO::PARAM_STR);
    $stmt->bindParam(":email",$email,PDO::PARAM_STR);


    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;
        
        foreach($rcrd AS $row) {
            $count++;
        }
        
        if ($count == 0) {
            $error   = true;
            $message = "OTP does not exist";
        } else {
            $stmt_insert = $pdo->prepare("UPDATE es_otp SET isUsed = :isUsed WHERE otp = :otp");
            $stmt_insert->bindParam(":isUsed",$isUsed,PDO::PARAM_STR);
            $stmt_insert->bindParam(":otp",$otp,PDO::PARAM_STR);

            if ($stmt_insert->execute()) {
                
                $stmt_insert = $pdo->prepare("UPDATE es_appaccount_registration SET `password` = MD5(:password) WHERE emailAddress = :email");
                $stmt_insert->bindParam(":password",$password,PDO::PARAM_STR);
                $stmt_insert->bindParam(":email",$email,PDO::PARAM_STR);
            

                if ($stmt_insert->execute()) {
                    $error   = false;
                    $message = "Password has been change. You may now login your new password";
                } else {
                    $error   = true;
                    $message = "Error changing password";
                }
            } else {
                $error   = true;
                $message = "Error validating OTP";
            }
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