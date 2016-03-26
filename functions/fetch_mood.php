<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$user_token = $_GET["user_token"];
$video_id = $_GET["video_id"];

$mood =  $db->query("SELECT vote_mood FROM votes v WHERE user_token='$user_token' AND video_index = '$video_id'")->fetch(PDO::FETCH_COLUMN);

if($mood == null){
	echo 0;
} else {
	echo $mood;
}
?>
