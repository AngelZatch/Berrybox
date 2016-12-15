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
		// Increment video count for user.
		$incrementSongs = $db->query("UPDATE user_stats
								SET stat_songs_submitted = stat_songs_submitted + 1
								WHERE user_token = '$next[history_user]'");

		$videos_submitted = $db->query("SELECT stat_songs_submitted FROM user_stats WHERE user_token = '$next[history_user]'")->fetch(PDO::FETCH_COLUMN);
		$compatible_badges_stmt = $db->query("SELECT badge_id, badge_code FROM badges WHERE badge_type = 'Share videos'");
		$compatible_badges = array();
		while($compatible_badge = $compatible_badges_stmt->fetch()){
			preg_match('/(\d*)-(\d*)/', $compatible_badge["badge_code"], $matches);
			$b = array (
				"id" => $compatible_badge["badge_id"],
				"rank" => $matches[1],
				"value" => $matches[2]
			);
			array_push($compatible_badges, $b);
		}

		$context = new ZMQContext();
		$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
		$socket->connect("tcp://localhost:5555");

		unlockBadge($db, $socket, $compatible_badges, 0, $next["history_user"], $videos_submitted);


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

function unlockBadge($db, $socket, $badge_array, $index, $user_token, $current_value){
	$badge_id = $badge_array[$index]["id"];
	$badge_rank = $badge_array[$index]["rank"];
	$badge_value = $badge_array[$index]["value"];
	if($current_value >= $badge_value){
		$has_badge = $db->query("SELECT entry_id FROM user_badges ub
						JOIN badges b ON ub.badge_id = b.badge_id
						WHERE user_token = '$user_token' AND b.badge_id ='$badge_id'")->rowCount();
		if($has_badge == 0){
			date_default_timezone_set('UTC');
			$time = date('Y-m-d H:i:s', time());
			// Register the badge in the table
			$db->query("INSERT INTO user_badges(user_token, badge_id, unlock_date) VALUES('$user_token', $badge_id, '$time')");
			// Notify the user of eventual badge unlock
			$badge_unlock_message = array(
				"packet_type" => "badge",
				"token" => $user_token,
				"badge_icon" => "videos_rank".$badge_rank,
				"badge_name" => $badge_value."_videos"
			);
			$socket->send(json_encode($badge_unlock_message));
		} else {
			$index++;
			unlockBadge($db, $socket, $badge_array, $index, $user_token, $current_value);
		}
	} else {
		return;
	}
}
?>
