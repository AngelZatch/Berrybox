<?php
include "db_connect.php";
session_start();
$db = PDOFactory::getConnection();

$link = $_POST["url"];
$time = date_create('now')->format('Y-m-d H:i:s');
$user = $_SESSION["token"];
$roomToken = $_POST["roomToken"];

try{
	$db->beginTransaction();

	// Fetch name of the video
	$content = file_get_contents("http://youtube.com/get_video_info?video_id=".$link);
	parse_str($content, $ytarr);
	$title = $ytarr['title'];

	$upload = $db->prepare("INSERT INTO roomHistory_$roomToken(history_link, video_name, history_time, history_user)
	VALUES(:link, :title, :time, :user)");
	$upload->bindParam(':link', $link);
	$upload->bindParam(':title', $title);
	$upload->bindParam(':time', $time);
	$upload->bindParam(':user', $user);
	$upload->execute();
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
