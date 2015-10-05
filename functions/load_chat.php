<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$token = $_POST["token"];
$load = $db->query("SELECT * FROM roomChat_$token s
					JOIN user u ON s.message_author = u.user_token
					JOIN user_preferences up ON s.message_author=up.up_user_id
					ORDER BY message_time ASC");
$creator = $db->query("SELECT room_creator FROM rooms WHERE room_token = '$token'")->fetch(PDO::FETCH_ASSOC);
$messageList = array();
while($message = $load->fetch(PDO::FETCH_ASSOC)){
	$m = array();
	$m["author"] = $message["user_pseudo"];
	if($message["message_author"] == $creator["room_creator"]){
		$m["status"] = 1;
	} else {
		$m["status"] = 2;
	}
	$m["authorColor"] = $message["up_color"];
	$m["timestamp"] = date_create($message["message_time"])->format('H:i');
	$m["content"] = stripslashes($message["message_contents"]);
	array_push($messageList, $m);
}
echo json_encode($messageList);
?>
