<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$token = $_POST["roomToken"];
$userPower = $_POST["userPower"];
$load = $db->query("SELECT history_link, video_name, history_start FROM roomHistory_$token
					WHERE video_status = 1
					ORDER BY room_history_id DESC
					LIMIT 1");
if($load->rowCount() != 0){
	$n = array();
	$loaded = $load->fetch(PDO::FETCH_ASSOC);
	$n["link"] = $loaded["history_link"];
	$n["title"] = stripslashes($loaded["video_name"]);
	$n["timestart"] = $loaded["history_start"];
	echo json_encode($n);
} else {
	$load = $db->query("SELECT history_link, video_name, room_history_id FROM roomHistory_$token
					WHERE video_status = 0
					ORDER BY room_history_id DESC
					LIMIT 1");

	$n = array();
	$loaded = $load->fetch(PDO::FETCH_ASSOC);
	$n["link"] = $loaded["history_link"];
	$n["title"] = stripslashes($loaded["video_name"]);
	$time = date_create('now', new datetimezone('UTC'))->format('Y-m-d H:i:s');
	$n["timestart"] = $time;
	if($userPower == 2){
		$playing = $db->query("UPDATE roomHistory_$token
							SET video_status='1',
							history_start = '$time'
							WHERE room_history_id='$loaded[room_history_id]'");
	}
	echo json_encode($n);
}
?>
