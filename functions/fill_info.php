<?php
session_start();
include "db_connect.php";
$db = PDOFactory::getConnection();
$user_lang = $_SESSION["user_lang"];
$user = $_SESSION["token"];
require_once "../languages/lang.".$user_lang.".php";

$index = $_POST["index"];
$name = addslashes($_POST["name"]);

$update = $db->query("UPDATE song_base SET video_name = '$name' WHERE song_base_id = '$index'");

$message_data = array(
	"packet_type" => "notification",
	"token" => $user,
	"notification_type" => "success",
	"content" => $lang["info_fill_success"]
);
$context = new ZMQContext();
$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
$socket->connect("tcp://localhost:5555");
$socket->send(json_encode($message_data));
?>
