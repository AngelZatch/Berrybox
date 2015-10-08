<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];
$userToken = $_POST["userToken"];
$timestamp = date_create('now')->format('Y-m-d H:i:s');
$state = 1;
$present = 1;

// Fetch the state of the user wanting to join
$search = $db->query("SELECT * FROM roomUsers_$roomToken WHERE room_user_token='$userToken'");
// If the user never joined the room
if($search->rowCount() == 0){
	try{
		$db->beginTransaction();
		$join = $db->prepare("INSERT INTO roomUsers_$roomToken(room_user_token, room_user_state, room_user_present, room_user_date_state)
VALUES(:token, :state, :present, :date)");
		$join->bindParam(':token', $userToken);
		$join->bindParam(':state', $state);
		$join->bindParam(':present', $present);
		$join->bindParam(':date', $timestamp);
		$join->execute();
		$db->commit();
		echo $state;
	}catch(PDOException $e){
		$db->rollBack();
	}
} else {
	// If the user left the room and is coming back
	$rejoin = $db->query("UPDATE roomUsers_$roomToken SET room_user_present=1 WHERE room_user_token='$userToken'");
	$search->fetch(PDO::FETCH_ASSOC);
	echo 2;
}
?>
