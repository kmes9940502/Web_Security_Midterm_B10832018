<?php
$db_password = getenv('MYSQL_PASSWORD', true);
$encrypt_password = getenv('AES_PASSWORD', true);
define('DB_SERVER', 'db');
define('DB_USERNAME', 'user');
define('DB_NAME', 'myDb');
define('DB_PASSWORD', $db_password);
define('AES_PASSWORD', $encrypt_password);
$link = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD, DB_NAME);
mysqli_query($link, 'SET NAMES utf8');
if($link === false){
    die("ERROR: Could not connect. " . mysqli_connect_error());
}
else{
    return $link;
}

?>