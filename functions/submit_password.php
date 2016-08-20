<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
$roomToken = $_POST["roomToken"];
$password = $_POST["password"];

$room = $db->query("SELECT * FROM rooms WHERE box_token='$roomToken'")->fetch(PDO::FETCH_ASSOC);

if($room["room_password"] == $password){
	echo 1;
} else {
	echo 2;
}
?>
