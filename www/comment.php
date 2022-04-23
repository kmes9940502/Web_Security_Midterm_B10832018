<?php
	include_once "config.php";
	session_start();
	$id = $_GET["id"];
    $sql="SELECT * FROM `comment` WHERE `id` = '$id'";
	$result = mysqli_query($link , $sql) or die('MySQL query error');
   	$row = mysqli_fetch_array($result);
	//if($_SESSION["username"]!=$row["author"])
    	
    
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <link rel="stylesheet" href="../css/App.css">

    <title>留言</title>
</head>

<body>
    <div id="root">
        <div class="App">
            <div class="CommentInfo"><span class="Author">
                    <nobr>作者：<?php echo $row["nickname"];?></nobr>
                </span><span class="Author">
                    <nobr>發布時間：<?php echo $row["time"];?></nobr>
                </span></div>
            <div class="TextPad">
                <p><?php echo $row["content"];?></p>
            </div>
            <p><?php if(isset($_SESSION["username"]) &&$_SESSION["username"]==$row["author"]){?>
                    <button class="SubmitButton" onclick="window.open('api.php?method=delete&id=<?php echo $row["id"] ?>','_self')">刪除</button>
                <?php }?>
            <button class="SubmitButton" onclick="window.open('index.php','_self')">返回</button>
            <?php if(isset($row["file"]) && $row["file"] != ""){ ?>
                <button class="SubmitButton" onclick="window.open('api.php?method=download&fl=<?php echo $row["file"] ?>','_self')">下載</button>
                <nobr><?php echo substr($row["file"], 5)?></nobr>
            <?php } ?>
            </p>

        </div>
    </div>


</body>


</html>