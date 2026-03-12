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

    $encryptedMessage = $_POST['Message'] ?? '';
    $idPost = $_POST['IdPost'] ?? '';

    $message = decryptAES($encryptedMessage, $secretKey);

    if ($message && $idPost != '') {
        $cleanMessage = $mysqli->real_escape_string($message);
        $cleanIdPost = intval($idPost);

        $sql = "INSERT INTO `comments` (`IdPost`, `Messages`) VALUES ('$cleanIdPost', '$cleanMessage')";
        
        if($mysqli->query($sql)) {
            echo "Success";
        } else {
            echo "Error: " . $mysqli->error;
        }
    } else {
        echo "Invalid data";
    }
?>