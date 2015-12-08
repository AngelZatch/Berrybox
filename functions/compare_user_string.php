<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
$string = $_POST["string"];

$compare = $db->query("SELECT * FROM user WHERE user_pseudo='$string'");

if($compare->rowCount() == 0){
	echo 1; // Success
} else {
	echo 2; // This username already exists
}
?>
