<?php 
    require_once "config.php";
    session_start();
    $sql = "SELECT * FROM `comment` ORDER BY `time` DESC";
	$result = mysqli_query($link , $sql) or die('MySQL query error');

    $sql = "SELECT * FROM `webtitle` WHERE `id` = 1";
    $titleResult = mysqli_query($link , $sql) or die('MySQL query error');
    
    $sql = "SELECT * FROM `account` WHERE `authority` = 1;";
    $accountRes = mysqli_query($link , $sql) or die('MySQL query error');
    

    $admin = mysqli_fetch_array($accountRes);
    $row = mysqli_fetch_array($titleResult);
    $webtitle = $row['title'];


    $account = "";
    $ShotSrc = "default.png";
    if(isset($_SESSION["username"])){
        $account=$_SESSION["username"];
        $sql = "SELECT `pic` FROM `account` WHERE `username` = '$account';";
        $shotResult = mysqli_query($link , $sql) or die('MySQL query error');
        
        $pic = mysqli_fetch_array($shotResult);
        $ShotSrc = $pic['pic'];
    }
    mysqli_close($link);
    
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" type='text/css' href="./css/App.css">


    <title>留言板</title>
</head>

<body>
    <div class="App">
        <h1 class="Title"><?php echo $webtitle ?> </h1>
        <div class="LoginButtonBoard">
            <?php if(isset($_SESSION["username"])){?>
                <div class="ShotBoard">
                    <img class="ShotImg" src="<?php echo $ShotSrc ?>">
                </div>
                <div>
                    <a href="api.php?method=logout"><button class="LoginButton">登出</button></a>
                    <?php if($admin['username'] === $_SESSION['username']){ ?>
                        <a href="manage.php"><button class="LoginButton">管理頁面</button></a>
                    <?php } ?>
                </div>
            <?php }else{?>
                <div>
                <a href="login.php"><button class="LoginButton">登入</button></a>
                <a href="signup.php"><button class="LoginButton">註冊</button></a>
                </div>
            <?php }?>
        </div>

        <form class="MessageForm" method="POST" enctype="multipart/form-data" action="api.php?method=add">
            <div class="MessageLable">留言內容</div>
            <textarea class="MessageTextArea" rows="10" placeholder="說點什麼......" name = "content"></textarea>
            <div>
            <?php if(isset($_SESSION["username"])){?>
			    <p>
                    <button class="SubmitButton">送出</button>
                    <input type="file" name="my_file">
                </p>
            <?php }?> 
            </div>
        </form>
        <?php while($row = mysqli_fetch_array($result)){ ?>
            <div class="MessageList" onclick="window.open('comment.php?id=<?php echo $row["id"] ?>','_self')" type="button" style="cursor: pointer">
                <div class="MessageContainer">
                    
                    <div class="MessageHead">
                        <div class="MessageAuthor">作者：<?php echo $row["nickname"];?></div>
                        <div class="MessageTime"><?php echo $row["nickname"];?></div>
                    </div>
                    <div class="MessageBody"> <?php echo $row["content"];?> </div>
                </div>
            </div>
        <?php } ?>
    </div>
</body>

</html>
