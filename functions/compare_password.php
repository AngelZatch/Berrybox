<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
$string = $_POST["string"];
$userToken = $_POST["userToken"];

$compare = $db->query("SELECT user_pwd FROM user WHERE user_token='$userToken'")->fetch(PDO::FETCH_ASSOC);

if(strcasecmp($compare["user_pwd"], $string) == 0){
	echo 1; // Success
} else {
	echo 2; // This username already exists
}
?>
