<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_POST["box_token"];

$stmt = $db->query("SELECT room_history_id, playlist_order, video_index, link, video_name, history_user, history_time, video_status, pending, user_pseudo FROM roomHistory_$box_token rh
					JOIN song_base sb ON rh.video_index = sb.song_base_id
					JOIN user u ON rh.history_user = u.user_token
					ORDER BY playlist_order DESC");
$history = array();
while($video = $stmt->fetch(PDO::FETCH_ASSOC)){
	$v = array(
		"entry" => $video["room_history_id"],
		"index" => $video["video_index"],
		"order" => $video["playlist_order"],
		"videoLink" => $video["link"],
		"videoName" => stripslashes($video["video_name"]),
		"submitter" => $video["user_pseudo"],
		"submitTime" => $video["history_time"],
		"videoStatus" => $video["video_status"],
		"pending" => $video["pending"]
	);
	array_push($history, $v);
}
echo json_encode($history);
?>
