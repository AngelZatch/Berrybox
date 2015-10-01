<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];
$userToken = $_POST["userToken"];
$timestamp = date_create('now')->format('Y-m-d H:i:s');
$state = 1;

$search = $db->query("SELECT * FROM roomUsers_$roomToken WHERE room_user_token='$userToken'");
if($search->rowCount() == 0){
	try{
		$db->beginTransaction();
		$join = $db->prepare("INSERT INTO roomUsers_$roomToken(room_user_token, room_user_state, room_user_date_state)
VALUES(:token, :state, :date)");
		$join->bindParam(':token', $userToken);
		$join->bindParam(':state', $state);
		$join->bindParam(':date', $timestamp);
		$join->execute();
		$db->commit();
	}catch(PDOException $e){
		$db->rollBack();
		echo $e->getMessage();
	}
}
?>
