<?php
    session_start();
    include("./settings/connect_datebase.php");
?>
<!DOCTYPE HTML>
<html>
    <head> 
        <meta charset="utf-8">
        <title> WEB-безопасность </title>
        <link rel="stylesheet" href="style.css">
        <script src="https://code.jquery.com/jquery-1.8.3.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.min.js"></script>
    </head>
    <body>
        <div class="top-menu">
            <?php if (!isset($_SESSION['user'])): ?>
                <a class="button" href="./login.php">Войти</a>
            <?php else: ?>
                <a class="button" href="./login.php">Выйти</a>
            <?php endif; ?>
        
            <a href="#"><img src="img/logo1.png"/></a>
            <div class="name">
                <a href="index.php">
                    <div class="subname">БЕЗОПАСНОСТЬ ВЕБ-ПРИЛОЖЕНИЙ</div>
                    Пермский авиационный техникум им. А. Д. Швецова
                </a>
            </div>
        </div>
        <div class="space"> </div>
        <div class="main">
            <div class="content">
                <div class="name">Новости:</div>
                
                <div>
                    <?php
                        $query_news = $mysqli->query("SELECT * FROM `news`;");
                        while($read_news = $query_news->fetch_assoc()) {
                            $QueryMessages = $mysqli->query("SELECT * FROM `comments` WHERE `IdPost` = {$read_news["id"]}");

                            echo '
                                <div class="specialty">
                                    <div class="slider">
                                        <div class="inner">
                                            <div class="name">'.$read_news["title"].'</div>
                                            <div class="description" style="overflow: auto;">
                                                <img src="'.$read_news["img"].'" style="width: 50px; float: left; margin-right: 10px;">
                                                '.$read_news["text"].'
                                            </div>
                                            <div class="messages">';
                                                while($ReadMessages = $QueryMessages->fetch_assoc()) {
                                                    echo "<div>".htmlspecialchars($ReadMessages["Messages"])."</div>";
                                                }
                                            echo '</div>';

                                            if (isset($_SESSION['user'])) {
                                                echo 
                                                    '<div class="messages" id="'.$read_news["id"].'">
                                                        <input type="text" style="width: 80%;">
                                                        <div class="button" style="float: right; margin-top: 0px;" onclick="SendMessage(this)">Отправить</div>
                                                    </div>';
                                            }
                                            
                                        echo '</div>
                                    </div>
                                </div>';
                        }
                    ?>
                    <div class="footer">
                        © КГАПОУ "Авиатехникум", 2020
                    </div>
                </div>
            </div>
        </div>
    </body>

    <script>
        const secretKey = "qazxswedcvfrtgbn";

        function encryptAES(data, key) {
            var keyHash = CryptoJS.MD5(key);
            var keyBytes = CryptoJS.enc.Hex.parse(keyHash.toString());
            var iv = CryptoJS.lib.WordArray.random(16);
            var encrypted = CryptoJS.AES.encrypt(data, keyBytes, {
                iv: iv,
                mode: CryptoJS.mode.CBC,
                padding: CryptoJS.pad.Pkcs7
            });
            var combined = iv.concat(encrypted.ciphertext);
            return CryptoJS.enc.Base64.stringify(combined);
        }

        function SendMessage(sender) {
            let inputField = sender.parentElement.children[0];
            let Message = inputField.value;
            let IdPost = sender.parentElement.id;
            
            if(Message == "") return;

            let encryptedMessage = encryptAES(Message, secretKey);

            var Data = new FormData();
            Data.append("Message", encryptedMessage);
            Data.append("IdPost", IdPost);
            
            $.ajax({
                url         : 'ajax/message.php',
                type        : 'POST',
                data        : Data,
                cache       : false,
                processData : false,
                contentType : false, 
                success: function (_data) {
                    console.log("Сервер ответил:", _data);
                    inputField.value = "";
                    let chatBox = sender.parentElement.parentElement.getElementsByClassName("messages")[0];
                    chatBox.innerHTML += "<div>" + Message + "</div>";
                },
                error: function() {
                    console.log('Системная ошибка!');
                }
            });
        }
    </script>
</html>