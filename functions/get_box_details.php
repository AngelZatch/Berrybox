<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_GET["box_token"];

$box_status = $db->query("SELECT box_token, room_active, room_creator, room_play_type, user_pseudo AS creator_pseudo FROM rooms r
						JOIN user u ON r.room_creator = u.user_token
						WHERE box_token = '$box_token'");

$feed = $box_status->fetch(PDO::FETCH_ASSOC);

echo json_encode($feed);
?>
