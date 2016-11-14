<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["box_token"];
$user_token = $_POST["user_token"];
date_default_timezone_set('UTC');
$time = date('Y-m-d H:i:s', time() - 15);
$state = 1;

if($user_token != -1){
	// Fetch the state of the user wanting to join
	$search = $db->query("SELECT * FROM roomUsers_$roomToken WHERE room_user_token='$user_token'");
	// If the user never joined the room
	if($search->rowCount() == 0){
		try{
			$db->beginTransaction();
			// Check if user is a moderator of the creator
			$checkStatus = $db->query("SELECT * FROM user_moderators
									WHERE moderator_token = '$user_token'
									AND user_token = (SELECT room_creator FROM rooms WHERE box_token = '$roomToken')");
			if($checkStatus->rowCount() != 0){
				$state = 3;
			}

			// Enter the room
			$join = $db->prepare("INSERT INTO roomUsers_$roomToken(room_user_token, room_user_state, presence_stamp)
VALUES(:token, :state, :date)");
			$join->bindParam(':token', $user_token);
			$join->bindParam(':state', $state);
			$join->bindParam(':date', $time);
			$join->execute();

			// Add a visitor to the number of total visitors for the creator of the room
			$addVisitor = $db->query("UPDATE user_stats
								SET stat_visitors = stat_visitors +1
								WHERE user_token = (SELECT room_creator FROM rooms WHERE box_token = '$roomToken')");
			$db->commit();
			echo $state;
		}catch(PDOException $e){
			$db->rollBack();
		}
	} else {
		// If the user left the room and is coming back
		$res = $search->fetch(PDO::FETCH_ASSOC);
		echo $res["room_user_state"];
	}
} else {
	$state = 1;
	echo $state;
}
?>
