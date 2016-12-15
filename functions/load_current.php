<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_POST["box_token"];
if(isset($_POST["user_power"]))
	$user_power = $_POST["user_power"];
else
	$user_power = -1;
$load = $db->query("SELECT video_index, history_start, link, video_name, user_pseudo FROM roomHistory_$box_token rh
					JOIN song_base sb ON rh.video_index = sb.song_base_id
					JOIN user u ON rh.history_user = u.user_token
					WHERE video_status = 1
					ORDER BY room_history_id DESC
					LIMIT 1");
if($load->rowCount() != 0){
	$loaded = $load->fetch(PDO::FETCH_ASSOC);
	$n = array(
		"index" => $loaded["video_index"],
		"link" => $loaded["link"],
		"title" => stripslashes($loaded["video_name"]),
		"timestart" => $loaded["history_start"],
		"submitter" => $loaded["user_pseudo"]
	);
	echo json_encode($n);
}
?>
