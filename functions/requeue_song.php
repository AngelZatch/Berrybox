<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
$roomToken = $_POST["roomToken"];
$id = $_POST["id"];
$userToken = $_POST["userToken"];
try{
	$requeue = $db->query("INSERT INTO roomHistory_$roomToken(history_link, video_name, history_time, history_user)
							SELECT history_link, video_name, history_time, '$userToken'
							FROM roomHistory_$roomToken
							WHERE room_history_id = '$id'");
	echo "1"; // Success code
} catch (PDOException $e){
	echo "2"; // Error code
}

?>
