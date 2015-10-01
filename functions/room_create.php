<?php
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();
$creatorToken = $_POST["creator"];
$roomName = $_POST["roomName"];

$type = 1;
$protect = 1;
$active = 1;
/** Add an entry into rooms table **/
$uniqueToken = generateReference(15);
$newRoom = $db->prepare("INSERT INTO rooms(room_token, room_name, room_creator, room_type, room_protection, room_active)
						VALUES(:token, :name, :creator, :type, :protection, :active)");
$newRoom->bindParam(':token', $uniqueToken);
$newRoom->bindParam(':name', $roomName);
$newRoom->bindParam(':creator', $creatorToken);
$newRoom->bindParam(':type', $type);
$newRoom->bindParam(':protection',$protect);
$newRoom->bindParam(':active', $active);
$newRoom->execute();

/** Create tables for the chat and history of all links posted. **/
try{
	$newHistory = $db->query("CREATE TABLE roomHistory_$uniqueToken(
room_history_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
history_link VARCHAR(300) NOT NULL,
history_time DATETIME,
history_user VARCHAR(10) NOT NULL
)");
	$newChat = $db->query("CREATE TABLE roomChat_$uniqueToken(
message_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
message_author VARCHAR(10) NOT NULL,
message_time DATETIME,
message_contents TEXT
)");
	$newUsersList = $db->query("CREATE TABLE roomUsers_$uniqueToken(
room_user_entry INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
room_user_token VARCHAR(10) NOT NULL,
room_user_state INT(11),
room_user_date_state DATETIME
)");

	// Put the creator of the room in the room
	$activeUser = $db->query("INSERT INTO roomUsers_$uniqueToken(room_user_token, room_user_state, room_user_date_state)
	VALUES(:token, :state, :date)");
	$initialState = 2; // Administrator
	$date = date_create('now')->format('Y-m-d H:i:s');
	$activeUser->bindParam(':token', $creatorToken);
	$activeUser->bindParam(':state', $initialState);
	$activeUser->bindParam(':token', $date);
	$activeUser->execute();

	echo $uniqueToken;
} catch (PDOException $e){
	echo $e->getMessage();
}

?>
