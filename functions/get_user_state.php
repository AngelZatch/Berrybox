<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$user_token = $_GET["user_token"];
$box_token = $_GET["box_token"];

$status = $db->query("SELECT room_user_state
					FROM roomUsers_$box_token
					WHERE room_user_token = '$user_token'")->fetch(PDO::FETCH_ASSOC);

echo json_encode($status);
?>
