<?php
	include 'conn.php';
	$pdo = new PDO($dsn, $user, $passwd);

	$email        = $_POST["emailuser"];
    $username     = $_POST["emailuser"];
	$password     = $_POST["password"];
	$error        = false;
    $message      = "Successfully Logged In";
    $response     = array();
    $result       = array();  

    $stmt = $pdo->prepare("
        SELECT
            a.id, 
            a.emailAddress,
            CONCAT(a.firstName,' ',a.middleName,' ',a.lastName) AS fullName,
            a.isRegularUser,
            CONCAT(a.streetName,' ',d.barangayName,' ',c.municipalityName,' (',c.zipCode,') ',b.province) AS fullAddress,
            IFNULL(e.imageLink,'-') AS imageLink
        FROM 
            es_appaccount_registration a
        INNER JOIN
            es_province b
        ON
            a.provinceID = b.id
        INNER JOIN
            es_municipality c
        ON
            a.municipalityID = c.id
        INNER JOIN
            es_barangay d
        ON
            a.barangayID = d.id 
        LEFT JOIN
            es_appaccount_ids e
        ON 
            a.id = e.userID AND e.isProfile = 1
        WHERE 
            (a.emailAddress = :email OR a.username = :username) AND a.password = MD5(:password) AND a.`status` IN ('Approved','Reactivated') AND a.isActive = 1
    ");

    $stmt->bindParam(":email",$email,PDO::PARAM_STR);
    $stmt->bindParam(":username",$username,PDO::PARAM_STR);
    $stmt->bindParam(":password",$password,PDO::PARAM_STR);


    if ($stmt->execute()) {
        $rcrd = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        $count = 0;
        
        foreach($rcrd AS $row) {
            $temp = array();
                
            $temp["id"]            = $row["id"];  
            $temp["emailAddress"]  = $row["emailAddress"];    
            $temp["fullName"]      = $row["fullName"];
            $temp["isRegularUser"] = $row["isRegularUser"];
            $temp["fullAddress"]   = $row["fullAddress"];
            $temp["imageLink"]     = $row["imageLink"];
            
            array_push($result, $temp);
            $count++;
        }
        
        if ($count == 0) {
            $error   = true;
            $message = "Invalid username and password";
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