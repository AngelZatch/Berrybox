<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$mood_id = $_POST["mood_id"];
$user_token = $_POST["user_token"];
$video_id = $_POST["video_id"];

// Check if the vote already exists
$existence = $db->query("SELECT vote_id FROM votes v WHERE user_token = '$user_token' AND video_index = '$video_id'")->rowCount();

if($existence != 0){
	$vote = $db->query("UPDATE votes
					SET vote_mood = $mood_id
					WHERE user_token = '$user_token' AND video_index = '$video_id'");
} else {
	$vote = $db->query("INSERT INTO votes(vote_mood, user_token, video_index)
						VALUES($mood_id, '$user_token', '$video_id')");
}
?>
