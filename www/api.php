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
    //不合格輸入處理
    //輸入為空白
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    if( !isset($_POST['username']) || !isset($_POST['password']) || $_POST['username']=="" || $_POST['password']=="" ){
        echo "<script type='text/javascript'>";
        echo "alert('請輸入字串');";
        echo "location.href='login.php';";
        echo "</script>";
        return;
    }
    if(StringCheck($username)===false || StringCheck($password) === false){
        echo "<script type='text/javascript'>";
        echo "alert('請使用英文大小寫或數字');";
        echo "location.href='login.php';";
        echo "</script>";
        return;
    }
    
    
    require_once('config.php');
    $stmt = $link->prepare("SELECT * FROM `account` WHERE `username` = ? and `password` = ?;");
    $stmt->bind_param("ss", $username, $password);
    $stmt->execute();
    $result = $stmt->get_result();
    try {
        $row = $result->fetch_assoc();   
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
        $stmt->close();
        $link->close();
    }
    catch (Exception $e) {
        #echo 'Caught exception: ', $e->getMessage(), '<br>';
        #echo 'Check credentials in config file at: ', $Mysql_config_location, '\n';
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
        return;
    }
    $username = htmlspecialchars($_POST['username']);
    $password = htmlspecialchars($_POST['password']);
    $name = htmlspecialchars($_POST['name']);
    if(StringCheck($username) == false || StringCheck($password) == false){
        echo "<script type='text/javascript'>";
        echo "alert('帳號密碼請使用英文大小寫或數字');";
        echo "location.href='signup.php';";
        echo "</script>";
        return;
    }
    //確認帳號是否已被註冊
    require_once('config.php');

    $stmt = $link->prepare("SELECT * FROM `account` WHERE `username` = ?;");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    $row = $result->fetch_assoc();
    if($row != ""){
        
        echo "<script type='text/javascript'>";
        echo "alert('此帳號已被註冊');";
        echo "location.href='signup.php';";
        echo "</script>";
        $stmt->close();
        $link->close();
        return;
    }else{//成功通過驗證
        //大頭照處理
        $finaldest = "";
        $dest = "shot/";
        //如果有上傳圖片
        //從檔案上傳
        if($_POST['from'] == "file"){
            $tempFile=$_FILES['img'];
            //確認上傳是否成功
            if ($tempFile['error'] === UPLOAD_ERR_OK){
                //確認檔案大小 < 1MB
                $exec = ImageProcessing($tempFile, $dest);
                if($exec === true){
                    $finaldest=$dest . $tempFile['name'];
                }
                else{
                    echo "<script type='text/javascript'>";
                    echo "location.href='signup.php';";
                    echo "</script>";
                    return;
                }   
            }
        }
        //從url上傳
        else{
            $url=$_POST["urlTest"];
            $filename = 'shot/' . basename($url);
            $tempname = 'shot/'.'temp'.basename($url);
            if(isImage($url) === false){
                echo "<script type='text/javascript'>";
                echo "alert('照片格式錯誤');";
                echo "location.href='signup.php';";
                echo "</script>";
                return;
            }
            $img=file_get_contents($url);
            if($img === false){
                echo "<script type='text/javascript'>";
                echo "alert('上傳照片失敗,請重新再試一次');";
                echo "location.href='signup.php';";
                echo "</script>";
                return;
            }
            else{
                file_put_contents($tempname,$img);
                breakImage($tempname, $filename);
                unlink($tempname);
                $finaldest = $filename;
            }
            
        }
        
        if($finaldest===""){
            $finaldest="default.png";
        }

        $auth = 0;
        $stmtinsert = $link->prepare("INSERT INTO `account` (`username`, `name`, `password`, `pic`, `authority`) VALUES (?, ?, ?, ?, ?)");
        $stmtinsert->bind_param("ssssi", $username, $name, $password, $finaldest, $auth);
        $stmtinsert->execute();
        echo "<script type='text/javascript'>";
        echo "alert('註冊成功,請登入');";
        echo "location.href='login.php';";
        echo "</script>";
    }
    $stmtinsert->close();
    $stmt->close();
    $link->close();

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
    $username = htmlspecialchars($_SESSION["username"]);
    $content = bbcodeconverter($_POST["content"]);

    $stmt = $link->prepare("SELECT * FROM `account` WHERE `username` = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    $nickname = $row['name'];
    if(!isset($_POST['content']) || $_POST['content']==""){
        $link->close();
        echo "<script type='text/javascript'>";
        echo "alert('留言內容不能為空');";
        echo "location.href='index.php';";
        echo "</script>";
        return;
    }
    if(!isset($_SESSION['username']) || $_SESSION['username']==""){
        $stmt->close();
        $link->close();
        echo "<script type='text/javascript'>";
        echo "alert('登入異常,請重新登入');";
        echo "location.href='index.php';";
        echo "</script>";
        return;
    }
    $time = date('Y-m-d H:i:s');

    //上傳檔案
    $tempFile=$_FILES['my_file'];
    $dest = "file/";
    $finaldest = "";
    if ($tempFile['error'] === UPLOAD_ERR_OK){
        //這邊要做上傳失敗不送出留言
        $success = UploadFile($tempFile);
        if($success === false){
            return;
        }
        $finaldest = $dest . $tempFile['name'];
    }


    $stmt = $link->prepare("INSERT INTO `comment` (`author`, `nickname`, `content`, `file`, `time`) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssss", $username, $nickname, $content, $finaldest, $time);
    $stmt->execute();

    $stmt->close();
    $link->close();
    header('Location: index.php');
}
function del(){
    require_once('config.php');
    session_start();
    $id = htmlspecialchars($_GET["id"]);

    $stmt = $link->prepare("SELECT `author`, `file` FROM `comment` WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    //刪除檔案
    if(file_exists($row["file"].".zip")){
        unlink($row["file"].".zip");
    }
    if(htmlspecialchars($_SESSION["username"]) == $row["author"]){
        $stmt = $link->prepare("DELETE FROM `comment` WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        
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
        return;
    }
    $stmt->close();
    $link->close();
    
}
function changetitle() {

    require_once('config.php');
    session_start();
    $title = htmlspecialchars($_POST['webtitle']);
    if(!isset($_POST['webtitle']) || $title == ""){
        echo "<script type='text/javascript'>";
        echo "alert('欄位不能為空');";
        echo "location.href='index.php';";
        echo "</script>";
        return;
    }

    $authority = 1;
    $username = htmlspecialchars($_SESSION["username"]);
    $stmt = $link->prepare("SELECT * FROM `account` WHERE `username` = ? AND `authority` = ?");
    $stmt->bind_param("si", $username, $authority);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    if($row == ""){
        header('Location: index.php');
        return;
    }

    $id = 1;
    $stmt = $link->prepare("UPDATE `webtitle` SET `title`=? WHERE `id`=?");
    $stmt->bind_param("si", $title, $id);
    $stmt->execute();
    echo "<script type='text/javascript'>";
    echo "alert('修改成功');";
    echo "location.href='index.php';";
    echo "</script>";
    $stmt->close();
    $link->close();
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
    
    # 檢查檔案是否合法
    if (file_exists('shot/' . $fileInput['name'])){
        echo "<script type='text/javascript'>";
        echo "alert('檔案已存在,請更改檔名');";
        echo "</script>";
        return false;
    }
    else if(($fileInput['size'] / 1024)> 1024){
        echo "<script type='text/javascript'>";
        echo "alert('照片大小限制為1MB');";
        echo "</script>";
        return false;
    }
    else if(isImage($fileInput['tmp_name']) === false){
        echo "<script type='text/javascript'>";
        echo "alert('照片格式錯誤');";
        echo "</script>";
        return false;
    }
    else {
        $file = $fileInput['tmp_name'];
        $dest = $dest . $fileInput['name'];
        //將檔案破壞並移至指定位置
        $suc = breakImage($file, $dest);
        if($suc === false){
            echo "<script type='text/javascript'>";
            echo "alert('照片出錯');";
            echo "</script>";
            return false;
        }
        
        return true;
    }
}
function UploadFile($tempFile) {

    require_once('config.php');
  //檢查檔案是否已經存在
  if (file_exists('file/' . $tempFile['name'])){
    echo "<script type='text/javascript'>";
    echo "alert('檔案已存在,請更改檔名');";
    echo "location.href='index.php';";
    echo "</script>";
    return false;
  } 
  else if(($tempFile['size'] / 1024)> 1024){
    echo "<script type='text/javascript'>";
    echo "alert('檔案過大,限制為1MB');";
    echo "location.href='index.php';";
    echo "</script>";
    return false;
  }
  else {
    $file = $tempFile['tmp_name'];
    $dest = 'file/' . $tempFile['name'].'.zip';
    $password = AES_PASSWORD;
    $output;
    $rc;
    exec("zip -P $password $dest $file", $outputs, $rc);
    return true;
    }


}

function DownloadFile(){
    require_once('config.php');

    if(isset($_GET["fl"])){
        $file = $_GET["fl"];
        
        if (file_exists($file.'.zip')) {
            $password = AES_PASSWORD;
            $zipFile = $file . '.zip';
            $unzipFilePath = "temp/";
            $rc;
            $output;
            exec("unzip -P $password -o $zipFile -d $unzipFilePath");
            $filename = glob('temp/tmp/*');
            rename( $filename[0], $unzipFilePath."tmp/".substr($file, 5));
            $newFileName = $unzipFilePath."tmp/".substr($file, 5);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename='.basename($newFileName));
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($newFileName));
            #ob_clean();
            flush();
            
            readfile($newFileName);
            unlink($newFileName);
            exit;
        }
    }
    else{
        echo "<script type='text/javascript'>";
        echo "alert('檔案獲取失敗,可能已損毀');";
        echo "location.href='index.php';";
        echo "</script>";
        return;
    }
}

function StringCheck($string) {
    if(preg_match('/(?=.*[a-zA-Z0-9])/', $string)){
        return true;
    } else{
        return false;
    }
}

function isImage($path)
{
	$a = getimagesize($path);
    try{
        if($a ==false){
            return false;
        }
        $image_type = $a[2];
        
        if(in_array($image_type , array(IMAGETYPE_GIF , IMAGETYPE_JPEG ,IMAGETYPE_PNG , IMAGETYPE_BMP)))
        {
            return true;
        }
        return false;
    }
    catch (Exception $e){
        return false;
    }
}
function setFile($msg, $dest)
{
    //取出目錄路徑中目錄(不包括後面的檔案)
    #$dir_name = dirname($dest);

    //如果目錄不存在就建立
    #if(!file_exists($dir_name)) {
    #    mkdir(iconv("UTF-8", "GBK", $dir_name), 0777, true);
    #}

    $fp = fopen($dest, "w");
    fwrite($fp, $msg);
    fclose($fp);
}
function breakImage($file, $dest){
    if(file_exists($file)){
        try{
            $imageTmp;
            $a = getimagesize($file);
            switch ($a[2]) {
                case IMAGETYPE_PNG:
                    $imageTmp=imagecreatefrompng($file);
                    break;
                case IMAGETYPE_JPEG:
                    $imageTmp=imagecreatefromjpeg($file);
                    break;
                case IMAGETYPE_GIF:
                    $imageTmp=imagecreatefromgif($file);
                    break;
                case IMAGETYPE_BMP:
                    $imageTmp=imagecreatefrombmp($file);
                    break;
                // Defaults to JPG
                default:
                    $imageTmp=imagecreatefromjpeg($file);
                    break;
            }
            if($imageTmp === false){
                return false;
            }
            $width = imagesx($imageTmp);
            $height = imagesy($imageTmp);
            if($width == 0 || $width == false || $height == 0 || $height == false){
                return false;
            }
            $numbersWidthTable = range(0,$width-1);
            $numbersHeightTable = range(0,$height-1);
            shuffle ($numbersWidthTable);
            shuffle ($numbersHeightTable);
            $amountW = ceil($width*0.1);
            $amountH = ceil($height*0.1);

            $numbersWidth =array_slice($numbersWidthTable,0,$amountW);
            $numbersHeight = array_slice($numbersHeightTable,0,$amountH);
            
            for ( $i = 1 ; $i < $amountW-1 ;$i++ ) {
                for($j = 1; $j < $amountH-1; $j++){
                    $color;
                    $dir = rand(0, 3);
                    $x = $numbersWidth[$i];
                    $y = $numbersHeight[$j];
                    switch($dir){
                        case 0:
                            if($x-1 >= 0)
                                $x = $x-1;
                            break;
                        case 1:
                            if($x+1 < $width)
                                $x = $x+1;
                            break;
                        case 2:
                            if($y-1 >= 0)
                                $y = $y-1;
                            break;
                        case 3:
                            if($y+1 < $height)
                                $y = $y+1;
                            break;
                    }
                    $color = imagecolorat($imageTmp, $x, $y);
                    imagesetpixel($imageTmp, $numbersWidth[$i],$numbersHeight[$j], $color);
                }
            }

            switch ($a[2]) {
                case IMAGETYPE_PNG:
                    imagepng($imageTmp, $dest);
                    break;
                case IMAGETYPE_JPEG:
                    imagejpeg($imageTmp, $dest);
                    break;
                case IMAGETYPE_GIF:
                    imagegif($imageTmp, $dest);
                    break;
                case IMAGETYPE_BMP:
                    imagebmp($imageTmp, $dest);
                    break;
                // Defaults to JPG
                default:
                    imagejpeg($imageTmp, $dest);
                    break;
            }

            #move_uploaded_file($imageTmp, $dest);
            imagedestroy($imageTmp);
            return true;
        }
        catch (Exception $e) {
            return false;
        }

    }
    
}

?>