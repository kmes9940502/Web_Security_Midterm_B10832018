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

    <title>登入</title>
</head>

<body>
    <div id="root">
        <div class="App">
            <h1 class="Title"> 登入 </h1>
            <form class="LoginForm" method="POST" action="api.php?method=login">
                <div class="LoginBoard">
                    <span>帳號：</span>
                    <input type="text" id="username" name="username">
                </div>
                <div class="LoginBoard">
                    <span>密碼：</span>
                    <input type="password" id="password" name="password">
                </div>
                <button class="LoginSendButton">登入</button>
                <a href="index.php"><button class="LoginSendButton" type="button">返回</button></a>
            </form>
        </div>
    </div>


</body>

</html>