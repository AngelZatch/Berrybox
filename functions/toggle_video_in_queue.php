<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_POST["box_token"];
$video_id = $_POST["video_id"];
$play_flag = $_POST["play_flag"];

$ignore = $db->query("UPDATE roomHistory_$box_token
					SET video_status = $play_flag
					WHERE room_history_id = $video_id");

$name = $db->query("SELECT video_index, video_name
					FROM roomHistory_$box_token rh
					JOIN song_base sb ON rh.video_index = sb.song_base_id
					WHERE room_history_id = $video_id")->fetch(PDO::FETCH_ASSOC);

echo $name["video_name"];
?>
