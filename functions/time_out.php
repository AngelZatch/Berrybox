<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
session_start();
include_once "../languages/lang.".$_SESSION["user_lang"].".php";

$targetToken = $_POST["targetToken"];
$roomToken = $_POST["roomToken"];
$now = date('Y-m-d H:i:s');

try{
	// Get number of timeouts
	$numberOfTimeouts = $db->query("SELECT room_user_timeouts FROM roomUsers_$roomToken WHERE room_user_token = '$targetToken'")->fetch(PDO::FETCH_ASSOC);

	switch($numberOfTimeouts["room_user_timeouts"]){
		case 1:
		$reset = date('Y-m-d H:i:s', time() + 15); // 1st t/o : 15 seconds
		break;

		case 2 :
		$reset = date('Y-m-d H:i:s', time() + 30); // 2nd t/o : 30 seconds
		break;

		case 3 :
		$reset = date('Y-m-d H:i:s', time() + 60); // 3rd t/o : 1 minute
		break;

		case 4 :
		$reset = date('Y-m-d H:i:s', time() + 5 * 60); // 4th t/o : 5 minutes
		break;

		case 5 :
		$reset = date('Y-m-d H:i:s', time() + 10 * 60); // 5th t/o : 10 minutes
		break;

		default:
		$reset = date('Y-m-d H:i:s', time() + 30 * 60); // 6th t/o and so on : 30 minutes
		break;
	}
	// Timeout action
	$timeout = $db->query("UPDATE roomUsers_$roomToken
							SET room_user_state = 4, room_user_timeouts = room_user_timeouts + 1, room_user_next_state_reset = '$reset'
							WHERE room_user_token = '$targetToken'");

	echo $numberOfTimeouts["room_user_timeouts"]++;
}catch(PDOException $e){
	echo $e->getMessage();
}
?>
