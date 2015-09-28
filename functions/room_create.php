<?php
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();
$creatorToken = $_POST["creator"];
$roomName = $_POST["roomName"];

$type = 1;
$protect = 1;
/** Add an entry into rooms table **/
$uniqueToken = generateReference(15);
$newRoom = $db->prepare("INSERT INTO rooms(room_token, room_name, room_creator, room_type, room_protection) VALUES(:token, :name, :creator, :type, :protection)");
$newRoom->bindParam(':token', $uniqueToken);
$newRoom->bindParam(':name', $roomName);
$newRoom->bindParam(':creator', $creatorToken);
$newRoom->bindParam(':type', $type);
$newRoom->bindParam(':protection',$protect);
$newRoom->execute();

/** Create tables for the chat and history of all links posted. **/
try{
	$newHistory = $db->query("CREATE TABLE roomHistory_$uniqueToken(
room_history_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
history_link VARCHAR(300) NOT NULL,
history_time DATETIME,
history_user INT(11) NOT NULL
)");
	$newChat = $db->query("CREATE TABLE roomChat_$uniqueToken(
message_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
message_author VARCHAR(10) NOT NULL,
message_time DATETIME,
message_contents TEXT
)");
	echo $uniqueToken;
} catch (PDOException $e){
	echo $e->getMessage();
}

?>
