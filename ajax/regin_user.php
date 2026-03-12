<?php
    session_start();
    include("../settings/connect_datebase.php");
    
    function decryptAES($encryptedData, $key) {
        $data = base64_decode($encryptedData);
        if ($data === false || strlen($data) < 17) return false;

        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        
        $keyHash = md5($key);
        $keyBytes = hex2bin($keyHash);

        return openssl_decrypt(
            $encrypted,
            'aes-128-cbc',
            $keyBytes,
            OPENSSL_RAW_DATA,
            $iv
        );
    }

    $secretKey = "qazxswedcvfrtgbn";

    $login = decryptAES($_POST['login'], $secretKey);
    $password = decryptAES($_POST['password'], $secretKey);

    if (!$login || !$password) {
        die("-1"); 
    }

    $query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$mysqli->real_escape_string($login)."'");
    
    if($query_user->num_rows > 0) {
        echo "-1"; 
    } else {
        $sql = "INSERT INTO `users`(`login`, `password`, `roll`) VALUES (
            '".$mysqli->real_escape_string($login)."', 
            '".$mysqli->real_escape_string($password)."', 
            0
        )";
        
        if($mysqli->query($sql)) {
            $id = $mysqli->insert_id;
            $_SESSION['user'] = $id;
            echo $id;
        } else {
            echo "-1";
        }
    }
?>