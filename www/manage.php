<?php require_once "config.php";
    session_start();
    if(!isset($_SESSION["username"]) || $_SESSION["username"] == ""){
        echo "<script type='text/javascript'>";
        echo "alert('登入異常,請重新登入');";
        echo "location.href='login.php';";
        echo "</script>";
    }
    
    $sql = "SELECT * FROM `account` WHERE `username` = '{$_SESSION["username"]}' AND `authority` = 1;";
    $result = mysqli_query($link , $sql) or die('MySQL query error');
    mysqli_close($link);
    $row = mysqli_fetch_array($result); 
    if($row == ""){
        echo "<script type='text/javascript'>";
        echo "alert('你沒有權限編輯這個頁面');";
        echo "location.href='index.php';";
        echo "</script>";
    }
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="../css/App.css">

    <title>管理頁面</title>
</head>

<body>
    <div id="root">
        <div class="App">
            <h1 class="Title">修改標題</h1>
            <form class="MessageForm" method="POST" action="api.php?method=title">
                <div>
                    <span>新標題：</span>
                    <input type="text" name="webtitle">
                </div>
                <div>
                    <button class="SubmitButton">送出</button>
                    <a href="index.php"><button class="SubmitButton" type="button">取消</button></a>
                </div>
            </form>
        </div>
    </div>

</body>

</html>