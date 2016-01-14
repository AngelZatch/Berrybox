<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];
$id = $_POST["id"];

$ignore = $db->query("UPDATE roomHistory_$roomToken
					SET video_status = 3
					WHERE room_history_id = $id");

$name = $db->query("SELECT video_index, video_name
					FROM roomHistory_$roomToken rh
					JOIN song_base sb ON rh.video_index = sb.song_base_id
					WHERE room_history_id = $id")->fetch(PDO::FETCH_ASSOC);

echo $name["video_name"];
?>
