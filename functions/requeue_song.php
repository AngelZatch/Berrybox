<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
$box_token = $_POST["box_token"];
$video_id = $_POST["video_id"];
$user_token = $_POST["user_token"];
try{
	$requeue = $db->query("INSERT INTO roomHistory_$box_token(video_index, history_time, history_user)
							SELECT video_index, history_time, '$user_token'
							FROM roomHistory_$box_token
							WHERE room_history_id = '$video_id'");
	echo "1"; // Success code
} catch (PDOException $e){
	echo "2"; // Error code
}

?>
