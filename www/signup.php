<?php require_once "config.php";
    session_start();
    if(isset($_SESSION["username"])){
        header("Location: index.php");
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="css/login.css">
    <link rel="stylesheet" href="css/App.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <title>註冊</title>
</head>

<body>
    <div id="root">
        <div class="App">
            <h1 class="Title"> 註冊帳號 </h1>
            <form class="LoginForm" method="POST" action="api.php?method=signup" enctype="multipart/form-data">
            <div class="SignupImgBoard"><img class="SignupShot" id="output"></div>
                <div>
                    <input type="radio" id="from" name="from" value="file" checked='checked'> 上傳
                    <input type="radio" id="from" name="from" value="url"> URL
                </div>
                <div id="BoxFile" name="BoxFile" class="BoxFile">
                    <input type="file" accept="image/*" onchange="loadFile(event)" name="img">
                </div>
                <div id="BoxUrl" name="BoxUrl" class="BoxUrl" >
                    <input type="text" id="urlTest" name="urlTest">
                    <button class="SubmitButton" type="button" name="imgUpload" id="imgUpload">上傳</button>
                </div>
                <script>
                    var loadFile = function(event) {
                        var output = document.getElementById('output');
                        output.src = URL.createObjectURL(event.target.files[0]);
                        output.onload = function() {
                        URL.revokeObjectURL(output.src) // free memory
                        }
                    };
                </script>
                
                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        $('input[type=radio][name="from"]').on('change', function() {
                        switch($(this).val()) {
                            case 'file':
                                $("#BoxFile").show()
                                $("#BoxUrl").hide()
                                break
                            case 'url':
                                $("#BoxFile").hide()
                                $("#BoxUrl").show()
                                break
                        }      
                        })
                    });   
                </script>

                <script type="text/javascript">
                    jQuery(document).ready(function(){
                        $('#imgUpload').on('click', function() {
                            let imgUrl=$("#urlTest").val()
                            var op = document.getElementById('output')
                            op.src = imgUrl
                           
                        })
                    });   
                </script>
                <div class="LoginBoard">
                    <span>暱稱：</span>
                    <input type="text" id="name" name="name">
                </div>
                <div class="LoginBoard">
                    <span>帳號：</span>
                    <input type="text" id="username" name="username">
                </div>
                <div class="LoginBoard">
                    <span>密碼：</span>
                    <input type="password" id="password" name="password">
                </div>
                <button class="LoginSendButton">註冊</button>
                <a href="index.php"><button class="LoginSendButton" type = "button">返回</button></a>
            </form>
        </div>
    </div>
</body>

</html>