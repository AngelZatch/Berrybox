<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$token = $_POST["token"];
// To reduce the stress on the client, messages from the last 30 minutes are loaded.
$now = date('Y-m-d H:i:s');
$limitDate = date('Y-m-d H:i:s', time() - 30 * 60);

$load = $db->query("SELECT * FROM roomChat_$token s
					JOIN user u ON s.message_author = u.user_token
					JOIN user_preferences up ON s.message_author=up.up_user_id
					WHERE message_time <= '$now' AND message_time > '$limitDate'
					ORDER BY message_time ASC");
$messageList = array();
while($message = $load->fetch(PDO::FETCH_ASSOC)){
	$m = array();
	$m["author"] = $message["user_pseudo"];
	$permission = $db->query("SELECT room_user_state FROM roomUsers_$token WHERE room_user_token = '$message[user_token]'")->fetch(PDO::FETCH_ASSOC);
	$m["status"] = $permission["room_user_state"];
	$m["authorColor"] = $message["up_color"];
	$m["timestamp"] = date_create($message["message_time"])->format('H:i');
	$m["content"] = stripslashes($message["message_contents"]);
	array_push($messageList, $m);
}
echo json_encode($messageList);
?>
