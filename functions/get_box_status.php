<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_GET["box_token"];

$box_status = $db->query("SELECT room_name, room_active, room_submission_rights, room_play_type, room_protection, COUNT(room_user_token) AS present_watchers
					FROM rooms, roomUsers_$box_token
					WHERE box_token = '$box_token' AND room_user_present = 1")->fetch(PDO::FETCH_ASSOC);


echo json_encode($box_status);
?>
