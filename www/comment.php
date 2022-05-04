<?php
	include_once "config.php";
	session_start();
	$id = htmlspecialchars($_GET["id"]);
    $stmt = $link->prepare("SELECT * FROM `comment` WHERE `id` = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();
    $link->close();
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
                <form class="MessageForm" method="POST" enctype="multipart/form-data" action="api.php?method=delete">
                    <input type="hidden" name="token" value="<?php echo $_SESSION['token'] ?? '' ?>">
                    <input type="hidden" name="id" value="<?php echo $row["id"] ?>">
                    <button class="SubmitButton" >刪除</button>

                </form>
                <?php }?>
            <button class="SubmitButton" onclick="window.open('index.php','_self')">返回</button>
            <?php if(isset($row["file"]) && $row["file"] != ""){ ?>
                <button class="SubmitButton" onclick="window.open('api.php?method=download&id=<?php echo $row["id"] ?>','_self')">下載</button>
                <nobr><?php echo substr($row["file"], 5)?></nobr>
            <?php } ?>
            </p>

        </div>
    </div>


</body>


</html>