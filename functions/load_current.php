<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$token = $_POST["roomToken"];
$load = $db->query("SELECT history_link, video_name FROM roomHistory_$token
					WHERE video_status = 1
					ORDER BY room_history_id DESC
					LIMIT 1");
if($load->rowCount() != 0){
	$n = array();
	$loaded = $load->fetch(PDO::FETCH_ASSOC);
	$n["link"] = $loaded["history_link"];
	$n["title"] = $loaded["video_name"];
	echo json_encode($n);
} else {
	$load = $db->query("SELECT history_link, video_name, room_history_id FROM roomHistory_$token
					WHERE video_status = 0
					ORDER BY room_history_id DESC
					LIMIT 1");

	$n = array();
	$loaded = $load->fetch(PDO::FETCH_ASSOC);
	$n["link"] = $loaded["history_link"];
	$n["title"] = $loaded["video_name"];
	$playing = $db->query("UPDATE roomHistory_$token
							SET video_status='1'
							WHERE room_history_id='$loaded[room_history_id]'");
	echo json_encode($n);
}
?>
