<?php
    session_start();
    include("../settings/connect_datebase.php");
    
    function decryptAES($encryptedData, $key) {
        $data = base64_decode($encryptedData);
        if ($data === false || strlen($data) < 17) return false;
        $iv = substr($data, 0, 16);
        $encrypted = substr($data, 16);
        $keyBytes = hex2bin(md5($key));
        return openssl_decrypt($encrypted, 'aes-128-cbc', $keyBytes, OPENSSL_RAW_DATA, $iv);
    }

    $secretKey = "qazxswedcvfrtgbn";
    $login_encrypted = $_POST['login'] ?? '';
    $login = decryptAES($login_encrypted, $secretKey);

    if (!$login) {
        die("-1");
    }

    $login = $mysqli->real_escape_string($login);
    $query_user = $mysqli->query("SELECT * FROM `users` WHERE `login`='".$login."';");
    
    $id = -1;
    if($user_read = $query_user->fetch_row()) {
        $id = $user_read[0];
    }
    
    function PasswordGeneration() {
        $chars="qazxswedcvfrtgbnhyujmkiolp1234567890QAZXSWEDCVFRTGBNHYUJMKIOLP";
        $max=10;
        $size=strlen($chars)-1;
        $password="";
        while($max--) {
            $password.=$chars[rand(0,$size)];
        }
        return $password;
    }
    
    if($id != -1) {
        $password = PasswordGeneration();
        $hashedPassword = md5($password);
        
        $mysqli->query("UPDATE `users` SET `password`='".$hashedPassword."' WHERE `login` = '".$login."'");
        
        echo $id;
    } else {
        echo "-1";
    }
?>