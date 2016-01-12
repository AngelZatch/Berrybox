<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];

$queryList = $db->query("SELECT * FROM roomHistory_$roomToken rh
					JOIN song_base sb ON rh.video_index = sb.song_base_id
					ORDER BY room_history_id DESC");
$songList = array();
while($song = $queryList->fetch(PDO::FETCH_ASSOC)){
	$s = array();
	$s["entry"] = $song["room_history_id"];
	$s["videoLink"] = $song["link"];
	$s["videoName"] = stripslashes($song["video_name"]);
	$s["submitter"] = $song["history_user"];
	$s["submitTime"] = $song["history_time"];
	$s["videoStatus"] = $song["video_status"];
	array_push($songList, $s);
}
echo json_encode($songList);
?>
