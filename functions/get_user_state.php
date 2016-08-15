<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$user_token = $_POST["user_token"];
$roomToken = $_POST["roomToken"];

$status = $db->query("SELECT room_user_present
					FROM roomUsers_$roomToken
					WHERE room_user_token = '$user_token'")->fetch(PDO::FETCH_ASSOC);

echo $status["room_user_present"];
?>
