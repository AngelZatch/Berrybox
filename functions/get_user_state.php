<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$userToken = $_POST["userToken"];
$roomToken = $_POST["roomToken"];

$status = $db->query("SELECT room_user_present
					FROM roomUsers_$roomToken
					WHERE room_user_token = '$userToken'")->fetch(PDO::FETCH_ASSOC);

echo $status["room_user_present"];
?>
