<?php
require_once "db_connect.php";
include "tools.php";
session_start();
$db = PDOFactory::getConnection();

$box_token = $_POST["box_token"];
$prev = $_POST["lastPlayed"];
$user_power = $_POST["user_power"];

if($user_power == 2){
	// Get ID of previous video
	$playedID = $db->query("SELECT room_history_id, playlist_order
							FROM roomHistory_$box_token rh
							WHERE video_index = '$prev'
							AND video_status = '1'")->fetch(PDO::FETCH_ASSOC);

	// Update status of previous video to 'played' (2)
	$played = $db->query("UPDATE roomHistory_$box_token
					SET video_status = '2'
					WHERE room_history_id = '$playedID[room_history_id]'");
}

// Get next video
$next = $db->query("SELECT room_history_id, video_index, history_user, user_pseudo, link, video_name, playlist_order
					FROM roomHistory_$box_token rh
					JOIN song_base sb ON rh.video_index = sb.song_base_id
					JOIN user u ON rh.history_user = u.user_token
					WHERE video_status = '0'
					ORDER BY playlist_order ASC
					LIMIT 1")->fetch(PDO::FETCH_ASSOC);

if($user_power == 2 && $next != null){
	// Ignoring skipped video, and giving them the "played" status
	$db->query("UPDATE roomHistory_$box_token
			SET video_status = '2'
			WHERE playlist_order < $next[playlist_order]
			AND video_status != '2'");
}

if($next["link"] != null){
	if($user_power == 2){
		$time = date_create('now', new datetimezone('UTC'))->format('Y-m-d H:i:s');
		// Set status of next video to 'playing' (1)
		$playing = $db->query("UPDATE roomHistory_$box_token
							SET video_status='1',
							history_start = '$time'
							WHERE room_history_id='$next[room_history_id]'");
		$incrementSongs = $db->query("UPDATE user_stats
								SET stat_songs_submitted = stat_songs_submitted + 1
								WHERE user_token = '$next[history_user]'");
		$db->query("UPDATE rooms SET room_active = 1, last_active_date = '$time' WHERE box_token = '$box_token'");

		// Pushing to socket
		$video_data = array(
			"packet_type" => "sync",
			"token" => $box_token,
			"index" => $next["video_index"],
			"link" => $next["link"],
			"title" => stripslashes($next["video_name"]),
			"timestart" => $time,
			"submitter" => $next["user_pseudo"]
		);
		$context = new ZMQContext();
		$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
		$socket->connect("tcp://localhost:5555");
		$socket->send(json_encode($video_data));
	}
	$n = array(
		"index" => $next["video_index"],
		"link" => $next["link"],
		"title" => stripslashes($next["video_name"]),
		"submitter" => $next["user_pseudo"]
	);
	echo json_encode($n);
}
?>
