<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$token = $_POST["token"];
$load = $db->query("SELECT * FROM roomChat_$token s
					JOIN user u ON s.message_author = u.user_token
					JOIN user_preferences up ON s.message_author=up.up_user_id
					ORDER BY message_time ASC");
$messageList = array();
while($message = $load->fetch(PDO::FETCH_ASSOC)){
	$m = array();
	$m["author"] = $message["user_pseudo"];
	$m["authorColor"] = $message["up_color"];
	$m["timestamp"] = date_create($message["message_time"])->format('H:i');
	$m["content"] = stripslashes($message["message_contents"]);
	array_push($messageList, $m);
}
echo json_encode($messageList);
?>
