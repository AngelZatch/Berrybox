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
	echo 0;
}
?>
