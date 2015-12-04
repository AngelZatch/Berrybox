<?php
include "db_connect.php";
session_start();
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];
$prev = $_POST["lastPlayed"];
$userPower = $_POST["userPower"];

if($userPower == 2){
	// Get ID of previous song
	$playedID = $db->query("SELECT room_history_id
							FROM roomHistory_$roomToken
							WHERE history_link = '$prev'
							AND video_status = '1'")->fetch(PDO::FETCH_ASSOC);

	// Update status of previous video to 'played' (2)
	$played = $db->query("UPDATE roomHistory_$roomToken
					SET video_status = '2'
					WHERE room_history_id = '$playedID[room_history_id]'");

	// Check if next is a video to ignore (video_status to 3)
	try{
		$nextVideoState = $db->query("SELECT video_status, room_history_id
									FROM roomHistory_$roomToken
									WHERE room_history_id = '$playedID[room_history_id]' +1")->fetch(PDO::FETCH_ASSOC);

		if($nextVideoState["video_status"] == '3'){
			// If it is, the video is indicated as played.
			$confirmIgnore = $db->query("UPDATE roomHistory_$roomToken
										SET video_status = '2'
										WHERE room_history_id = '$nextVideoState[room_history_id]'");
		}
	} catch (PDOException $e){
		echo $e->getMessage();
	}

}

// Get next video
$next = $db->query("SELECT room_history_id, history_link, video_name, history_user FROM roomHistory_$roomToken
					WHERE video_status = '0'
					ORDER BY room_history_id ASC
					LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if($next["history_link"] != null){
	if($userPower == 2){
		$time = date_create('now', new datetimezone('UTC'))->format('Y-m-d H:i:s');
		// Set status of next video to 'playing' (1)
		$playing = $db->query("UPDATE roomHistory_$roomToken
							SET video_status='1',
							history_start = '$time'
							WHERE room_history_id='$next[room_history_id]'");
		$incrementSongs = $db->query("UPDATE user_stats
								SET stat_songs_submitted = stat_songs_submitted + 1
								WHERE user_token = '$next[history_user]'");
	}
	$n = array();
	$n["link"] = $next["history_link"];
	$n["title"] = stripslashes($next["video_name"]);
	echo json_encode($n);
} else {
	echo null;
}
?>
