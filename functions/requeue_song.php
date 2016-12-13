<?php
session_start();
include "db_connect.php";
$db = PDOFactory::getConnection();
$box_token = $_POST["box_token"];
$video_id = $_POST["video_id"];
$user_token = $_POST["user_token"];
$user_lang = $_SESSION["user_lang"];

require_once "../languages/lang.".$user_lang.".php";

try{
	include "tools.php";
	$playlist_order = getPlaylistOrdering($db, $box_token);

	$requeue = $db->query("INSERT INTO roomHistory_$box_token(video_index, history_time, history_user, playlist_order)
							SELECT video_index, history_time, '$user_token', '$playlist_order'
							FROM roomHistory_$box_token
							WHERE room_history_id = '$video_id'");

	$message_data = array(
		"packet_type" => "notification",
		"token" => $user_token,
		"notification_type" => "success",
		"content" => $lang["song_submit_success"]
	);
} catch (PDOException $e){
	$message_data = array(
		"packet_type" => "notification",
		"token" => $user_token,
		"notification_type" => "warning",
		"content" => $lang["no_fetch"]
	);
}
// Pushing
$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://localhost:5555");
$socket->send(json_encode($message_data));

?>
