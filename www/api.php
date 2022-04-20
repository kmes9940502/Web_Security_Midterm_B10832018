<?php

switch ($_GET["method"]) {
    case "login":
        login();
        break;
    case "signup":
        signup();
        break;
    case "logout":
        logout();
        break;
    case "add":
        addcomment();
        break;
    case "title":
        changetitle();
        break;
    case "delete":
        del();
        break;
    case "download":
        DownloadFile();
        break;
    default:
        break;
}
//TODO:這邊要做字串驗證
function login() {

    if( !isset($_POST['username']) || !isset($_POST['password']) || $_POST['username']=="" || $_POST['password']=="" ){
        
    }
    $username = $_POST['username'];
    $password = $_POST['password'];
    require_once('config.php');
    $sql = "SELECT * FROM `account` WHERE `username` = '$username' and `password` = '$password';";
    
    $result=mysqli_query($link,$sql);
    mysqli_close($link);
    try {
        $row = mysqli_fetch_array($result);   
        if($row){
            session_start();
	    	$_SESSION["username"] = $row['username'];
            header("Location: index.php");
        }else{
            echo "<script type='text/javascript'>";
            echo "alert('登入失敗');";
            echo "location.href='login.php';";
            echo "</script>";
        }
    }
    catch (Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), '<br>';
        echo 'Check credentials in config file at: ', $Mysql_config_location, '\n';
    }
}
//TODO:要做輸入字串驗證
//上傳檔案
function signup() {

    //輸入帳號密碼確認
    if( !isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['name']) 
    || $_POST['username']=="" || $_POST['password']=="" || $_POST['name']==""){
        echo "<script type='text/javascript'>";
        echo "alert('欄位不能為空');";
        echo "location.href='signup.php';";
        echo "</script>";
    }
    $username = $_POST['username'];
    $password = $_POST['password'];
    $name = $_POST['name'];
    //確認帳號是否已被註冊
    require_once('config.php');
    $sql = "SELECT * FROM `account` WHERE `username` = '$username';";
    
    $result=mysqli_query($link,$sql);
    $row = mysqli_fetch_array($result);
    
    if($row != ""){
        mysqli_close($link);
        echo "<script type='text/javascript'>";
        echo "alert('此帳號已被註冊');";
        echo "location.href='signup.php';";
        echo "</script>";
    }else{//成功通過驗證
        //大頭照處理
        $finaldest = "";
        $dest = "shot/";
        //從檔案上傳
        if($_POST['from'] == "file"){
            $tempFile=$_FILES['img'];
            //確認上傳是否成功
            if ($tempFile['error'] === UPLOAD_ERR_OK){
                //確認檔案大小 < 1MB
                if(($tempFile['size'] / 1024)< 1024){
                    ImageProcessing($tempFile, $dest);
                    $finaldest=$dest . $tempFile['name'];
                }
                else{
                    echo "<script type='text/javascript'>";
                    echo "alert('照片大小限制為1MB');";
                    echo "location.href='signup.php';";
                    echo "</script>";
                }   
            }
            else {
                echo '錯誤代碼：' . $tempFile['error'] . '<br/>';
            }
        }
        //從url上傳
        else{
            $url=$_POST["urlTest"];
            $filename = 'shot/' . basename($url);
            $img=file_get_contents($url);
            file_put_contents($filename,$img);
            $finaldest = $filename;
        }
        
        if($finaldest===""){
            $finaldest="default.png";
        }
        
        $sql="INSERT INTO `account` (`username`, `name`, `password`, `pic`, `authority`) VALUES ('$username', '$name', '$password', '$finaldest', '0')";
        $result = mysqli_query($link , $sql) or die("MySQL query error");
        mysqli_close($link);
        echo "<script type='text/javascript'>";
        echo "alert('註冊成功,請登入');";
        echo "location.href='login.php';";
        echo "</script>";
    }

}

function logout() {
    session_start();
    if(isset($_SESSION["username"])){
        session_destroy();
        echo "<script type='text/javascript'>";
        echo "alert('登出成功');";
        echo "location.href='index.php';";
        echo "</script>";
    }
} 
//TODO:實作檔案上傳
function addcomment() {

    require_once('config.php');

    session_start();
    $username = $_SESSION["username"];
    $content = bbcodeconverter($_POST["content"]);
    $sql = "SELECT * FROM `account` WHERE `username` = '$username';";
    $result=mysqli_query($link,$sql);
    $row = mysqli_fetch_array($result);
    $nickname = $row['name'];
    if(!isset($_POST['content']) || $_POST['content']==""){
        echo "<script type='text/javascript'>";
        echo "alert('留言內容不能為空');";
        echo "location.href='index.php';";
        echo "</script>";
    }
    if(!isset($_SESSION['username']) || $_SESSION['username']==""){
        echo "<script type='text/javascript'>";
        echo "alert('登入異常,請重新登入');";
        echo "location.href='index.php';";
        echo "</script>";
    }
    $time = date('Y-m-d H:i:s');

    //上傳檔案
    $tempFile=$_FILES['my_file'];
    $dest = "file/";
    $finaldest = "";
    if ($tempFile['error'] === UPLOAD_ERR_OK){
        UploadFile($tempFile);
        $finaldest = $dest . $tempFile['name'];
    }


    $sql = "INSERT INTO `comment` (`author`, `nickname`, `content`, `file`, `time`) VALUES ('$username', '$nickname', '$content', '$finaldest', '$time')";
    $result = mysqli_query($link , $sql) or die('MySQL query error');
    mysqli_close($link);
    echo "<script type='text/javascript'>";
    echo "location.href='index.php';";
    echo "</script>";
}
function del(){
    require_once('config.php');
    session_start();
    $id = $_GET["id"];
    $sql = "SELECT `author` FROM `comment` WHERE id = $id";
    $result = mysqli_query($link , $sql) or die('MySQL query error');
    $row = mysqli_fetch_array($result);

    if($_SESSION["username"] == $row["author"]){
        $sql = "DELETE FROM `comment` WHERE id = $id";
        $result = mysqli_query($link , $sql) or die('MySQL query error');
        mysqli_close($link);
        echo "<script type='text/javascript'>";
        echo "alert('刪除留言成功');";
        echo "location.href='index.php';";
        echo "</script>";
    }
    else{
        echo "<script type='text/javascript'>";
        echo "alert('你沒有權限刪除這則留言');";
        echo "location.href='index.php';";
        echo "</script>";
    }
    
}
function changetitle() {

    require_once('config.php');

    $title = $_POST['webtitle'];
    if(!isset($_POST['webtitle']) || $title == ""){
        echo "<script type='text/javascript'>";
        echo "alert('欄位不能為空');";
        echo "location.href='index.php';";
        echo "</script>";
    }
    $sql = "UPDATE `webtitle` SET `title`='$title' WHERE `id`=1";
    $result = mysqli_query($link , $sql) or die('MySQL query error');
    mysqli_close($link);
        echo "<script type='text/javascript'>";
        echo "alert('修改成功');";
        echo "location.href='index.php';";
        echo "</script>";
    
}

function bbcodeconverter($text) {
    $text = htmlspecialchars($text);
    // BBcode array
    $find = array(
    '~\[b\](.*?)\[/b\]~s',
    '~\[i\](.*?)\[/i\]~s',
    '~\[u\](.*?)\[/u\]~s',
    '~\[color=([^"><]*?)\](.*?)\[/color\]~s',
    '~\[img\](https?://[^"><]*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s'
    );
    // HTML tags to replace BBcode
    $replace = array(
    '<b>$1</b>',
    '<i>$1</i>',
    '<span style="text-decoration:underline;">$1</span>',
    '<span style="color:$1;">$2</span>',
    '<img src="$1" alt="" />'
    );
    // Replacing the BBcodes with corresponding HTML tags
    return nl2br(preg_replace($find, $replace, $text));
}

function ImageProcessing($fileInput, $dest){
    //echo '檔案名稱: ' . $fileInput['name'] . '<br/>';
    //echo '檔案類型: ' . $fileInput['type'] . '<br/>';
    //echo '檔案大小: ' . ($fileInput['size'] / 1024) . ' KB<br/>';
    //echo '暫存名稱: ' . $fileInput['tmp_name'] . '<br/>';
    
    # 檢查檔案是否已經存在
    if (file_exists('shot/' . $fileInput['name'])){
        echo "<script type='text/javascript'>";
        echo "alert('檔案已存在,請更改檔名');";
        echo "</script>";
    } else {
        $file = $fileInput['tmp_name'];
        $dest = $dest . $fileInput['name'];
    
        //將檔案移至指定位置
        move_uploaded_file($file, $dest);
    }
}
function UploadFile($tempFile) {

  //檢查檔案是否已經存在
  if (file_exists('file/' . $tempFile['name'])){
    echo "<script type='text/javascript'>";
    echo "alert('檔案已存在,請更改檔名');";
    echo "</script>";
  } else {
    $file = $tempFile['tmp_name'];
    $dest = 'file/' . $tempFile['name'];
    
    //將檔案移至指定位置
    move_uploaded_file($file, $dest);
  }

}

function DownloadFile(){
    if(isset($_GET["file"])){
        $file = $_GET["file"];
        if (file_exists($file)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($file));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($file));
            ob_clean();
            flush();
            readfile($file);
            exit;
        }
    }
    else{
        echo "<script type='text/javascript'>";
        echo "alert('檔案獲取失敗,可能已損毀');";
        echo "location.href='index.php';";
        echo "</script>";
    }
    
 
    
}
?>