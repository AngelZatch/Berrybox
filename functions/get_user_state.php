<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$user_token = $_GET["user_token"];
$roomToken = $_GET["box_token"];

$status = $db->query("SELECT room_user_present, room_user_state
					FROM roomUsers_$roomToken
					WHERE room_user_token = '$user_token'")->fetch(PDO::FETCH_ASSOC);

echo json_encode($status);
?>
