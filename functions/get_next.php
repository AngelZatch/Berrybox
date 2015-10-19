<?php
include "db_connect.php";
session_start();
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];
$prev = $_POST["lastPlayed"];

// Update status of previous video to 'played' (2)
$played = $db->query("UPDATE roomHistory_$roomToken
					SET video_status='2'
					WHERE history_link = '$prev'
					AND video_status = '1'");

// Get next video
$next = $db->query("SELECT room_history_id, history_link, video_name FROM roomHistory_$roomToken
					WHERE video_status = '0'
					ORDER BY room_history_id ASC
					LIMIT 1")->fetch(PDO::FETCH_ASSOC);

// Set status of next video to 'playing' (1)
if($next["history_link"] != null){
	$playing = $db->query("UPDATE roomHistory_$roomToken
							SET video_status='1'
							WHERE room_history_id='$next[room_history_id]'");
	$n = array();
	$n["link"] = $next["history_link"];
	$n["title"] = $next["video_name"];
	echo json_encode($n);
} else {
	echo null;
}
?>
