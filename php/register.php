<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$isRegularUser  = $_POST["isRegularUser"];
	$firstName      = $_POST["firstName"];
	$middleName     = $_POST["middleName"];
	$lastName       = $_POST["lastName"];
	$mobileNumber   = $_POST["mobileNumber"];
	$provinceID     = $_POST["provinceID"];
	$municipalityID = $_POST["municipalityID"];
	$barangayID     = $_POST["barangayID"];
	$birthDate      = date("Y-m-d", strtotime($_POST["birthDate"]));
	$gender         = $_POST["gender"];
	$streetName     = $_POST["streetName"];
	$username       = $_POST["username"];
	$emailAddress   = $_POST["emailAddress"];
	$password       = $_POST["password"];
	$status         = $isRegularUser == 1 ? 'Approved' : 'Pending'; 
	$dateCreated    = $global_date;
	$serviceIDs     = explode(',', $_POST["serviceIDs"]); 
	$imageLinks     = explode(',', $_POST["imageLinks"]); 

	$response = array();
    $result   = array();
    $error    = false;
    $message  = "";

    $stmt_find = $pdo->prepare("SELECT * FROM es_appaccount_registration WHERE emailAddress = :emailAddress");
    $stmt_find->bindParam(":emailAddress",$emailAddress,PDO::PARAM_STR);

    if ($stmt_find->execute()) {
        $row = $stmt_find->fetch(PDO::FETCH_ASSOC);
        $stmt_find->closeCursor();

        if ($row) {
            $error = true;
            $message = "Email Address already exist";
        } else {
            $stmt_find = $pdo->prepare("SELECT * FROM es_appaccount_registration WHERE username = :username");
            $stmt_find->bindParam(":username",$username,PDO::PARAM_STR);

            if ($stmt_find->execute()) {
                $row = $stmt_find->fetch(PDO::FETCH_ASSOC);
                $stmt_find->closeCursor();

                if ($row) {
                    $error = true;
                    $message = "Username already exist";
                } else {
                	$stmt_insert = $pdo->prepare("INSERT INTO es_appaccount_registration 
                    	(isRegularUser,firstName,middleName,lastName,mobileNumber,provinceID,municipalityID,barangayID,birthDate,gender,streetName,username,emailAddress,password,status,dateCreated) 
                    	VALUES 
                    	(:isRegularUser,:firstName,:middleName,:lastName,:mobileNumber,:provinceID,:municipalityID,:barangayID,:birthDate,:gender,:streetName,:username,:emailAddress,MD5(:password),:status,:dateCreated) 
                    ");
                    $stmt_insert->bindParam(":isRegularUser",$isRegularUser,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":firstName",$firstName,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":middleName",$middleName,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":lastName",$lastName,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":mobileNumber",$mobileNumber,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":provinceID",$provinceID,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":municipalityID",$municipalityID,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":barangayID",$barangayID,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":birthDate",$birthDate,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":gender",$gender,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":streetName",$streetName,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":username",$username,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":emailAddress",$emailAddress,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":password",$password,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":status",$status,PDO::PARAM_STR);
                    $stmt_insert->bindParam(":dateCreated",$dateCreated,PDO::PARAM_STR);

                    if ($stmt_insert->execute()) {
                        $id = $pdo->lastInsertId();
                        $queryServiceID = array();

                        foreach($serviceIDs as $serviceID) {
                        	array_push($queryServiceID, "(".$id.",".$serviceID.")");
						}

						$stmt_insert = $pdo->prepare('INSERT INTO es_appaccount_services (userID,serviceID) VALUES ' . join(",",$queryServiceID));
						if ($stmt_insert->execute()) {
							$queryImageLink = array();
	                        foreach($imageLinks as $imageLink) {
	                        	array_push($queryImageLink, "(".$id.",'".$imageLink."')");
							}

							
							$stmt_insert = $pdo->prepare('INSERT INTO es_appaccount_ids (userID,imageLink) VALUES ' . join(",",$queryImageLink));
							if ($stmt_insert->execute()) {
								$error   = false;
								$continue_message = $isRegularUser == 1 ? 'You may now login' : "Wait for the admins approval that will be sent to your email";
                        		$message = "Successfully registered an account. " . $continue_message;
							} else {
								$error   = true;
                        		$message = "Error saving Image Link";
							}
						} else {
							$error   = true;
                        	$message = "Error saving Servce IDs : " . join(",",$queryServiceID);
						}
                    } else {
                        $error   = true;
                        $message = "Error saving account details";
                    }
                }
            } else {
            	$error = true;
       			$message = "Error checking username";
            }
        }
    } else {
    	$error = true;
        $message = "Error checking email address";
    }

    $response["error"]   = $error;
    $response["message"] = $message;
    $response["result"]  = $result;
    
    echo json_encode($response);
?>