<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["box_token"];

$queryList = $db->query("SELECT * FROM roomHistory_$roomToken rh
					JOIN song_base sb ON rh.video_index = sb.song_base_id
					ORDER BY playlist_order DESC");
$songList = array();
while($song = $queryList->fetch(PDO::FETCH_ASSOC)){
	$s = array(
		"entry" => $song["room_history_id"],
		"index" => $song["video_index"],
		"order" => $song["playlist_order"],
		"videoLink" => $song["link"],
		"videoName" => stripslashes($song["video_name"]),
		"submitter" => $song["history_user"],
		"submitTime" => $song["history_time"],
		"videoStatus" => $song["video_status"],
		"pending" => $song["pending"]
	);
	array_push($songList, $s);
}
echo json_encode($songList);
?>
