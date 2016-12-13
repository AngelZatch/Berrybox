<?php
session_start();
include "db_connect.php";
$db = PDOFactory::getConnection();
$user_lang = $_SESSION["user_lang"];

require_once "../languages/lang.".$user_lang.".php";

$box_token = $_GET["box_token"];

$queue = $db->query("SELECT room_history_id FROM roomHistory_$box_token WHERE video_status = 0 OR video_status = 3 ORDER BY room_history_id ASC")->fetchAll();
$order = $db->query("SELECT playlist_order FROM roomHistory_$box_token WHERE video_status = 0 OR video_status = 3 ORDER BY room_history_id ASC")->fetchAll(PDO::FETCH_COLUMN);

$numbers = range(min($order), max($order));
shuffle($numbers);

$i = 0;
foreach($numbers as $number){
	$current_id = $queue[$i]["room_history_id"];
	try{
		$db->query("UPDATE roomHistory_$box_token SET playlist_order = $number WHERE room_history_id = $current_id");
	}catch(PDOException $e){
		echo $e->getMessage();
	}
	$i++;
}

$message_data = array(
	"packet_type" => "notification",
	"token" => $_SESSION["token"],
	"notification_type" => "info",
	"content" => $lang["playlist_shuffled"]
);
$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://localhost:5555");
$socket->send(json_encode($message_data));
?>
