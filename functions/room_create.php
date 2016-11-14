<?php
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();
$creatorToken = $_POST["creator"];
$roomName = $_POST["roomName"];
$protect = $_POST["protect"];
$type = $_POST["type"];
$language = $_POST["language"];
$description = $_POST["description"];

$play_type = 1;
$active = 1;
$initialState = 2; // Administrator
date_default_timezone_set('UTC');
$time = date('Y-m-d H:i:s', time());

/** Add an entry into rooms table **/
$uniqueToken = generateReference(15);

/** Create tables for the chat and history of all links posted. **/
try{
	$db->beginTransaction();
	$newRoom = $db->prepare("INSERT INTO rooms(box_token, room_name, room_creator, creation_date, room_type, room_lang, room_description, room_play_type, room_protection, room_active, last_active_date)
						VALUES(:token, :name, :creator, :creation_date, :type, :room_lang, :description, :play_type, :protection, :active, :active_date)");
	$newRoom->bindParam(':token', $uniqueToken);
	$newRoom->bindParam(':name', $roomName);
	$newRoom->bindParam(':creator', $creatorToken);
	$newRoom->bindParam(':creation_date', $time);
	$newRoom->bindParam(':type', $type);
	$newRoom->bindParam(':room_lang', $language);
	$newRoom->bindParam(':description', $description);
	$newRoom->bindParam(':play_type', $play_type);
	$newRoom->bindParam(':protection',$protect);
	$newRoom->bindParam(':active', $active);
	$newRoom->bindParam(':active_date', $time);
	$newRoom->execute();

	$newHistory = $db->query("CREATE TABLE roomHistory_$uniqueToken(
room_history_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
playlist_order INT(11) COMMENT 'order of play',
video_index INT(11) COMMENT 'id in song_base table',
history_time DATETIME COMMENT 'submission timestamp',
history_start DATETIME COMMENT 'play beginning timestamp',
history_user VARCHAR(10) NOT NULL,
video_status TINYINT(1) NOT NULL DEFAULT '0' COMMENT '0 : queued / 1 : playing / 2 : played / 3 : ignored'
)");
	$newChat = $db->query("CREATE TABLE roomChat_$uniqueToken(
message_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
message_scope TINYINT(1) COMMENT '1 : all / 2 : creator / 3 : moderators / 4 : system / 5 : solo / 6: whisper',
message_type TINYINT(1) COMMENT '(subtype of scope 4); 1 : normal / 2 : play / 3 : skip / 4 : close / 5 : ignore / 6 : reinstate / 7 : reopen',
message_author VARCHAR(10) NOT NULL,
message_destination VARCHAR(10),
message_time DATETIME,
message_contents TEXT
)");
	$newUsersList = $db->query("CREATE TABLE roomUsers_$uniqueToken(
room_user_entry INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
room_user_token VARCHAR(10) NOT NULL,
room_user_state TINYINT(1) COMMENT '1 : standard / 2 : creator / 3 : moderator / 4 : toed / 5 : banned',
room_user_timeouts INT(11) NOT NULL DEFAULT '0' COMMENT 'number of timeouts of this user',
presence_stamp DATETIME,
room_user_next_state_reset DATETIME
)");

	// Add 1 to the stat of rooms created by this user
	$incrementStat = $db->query("UPDATE user_stats
								SET stat_rooms_created = stat_rooms_created + 1
								WHERE user_token = '$creatorToken'");

	// Put the creator of the room in the room
	$activeUser = $db->prepare("INSERT INTO roomUsers_$uniqueToken(room_user_token, room_user_state, presence_stamp)
							VALUES(:token, :state, :date)");
	$activeUser->bindParam(':token', $creatorToken);
	$activeUser->bindParam(':state', $initialState);
	$activeUser->bindParam(':date', $time);
	$activeUser->execute();
	$db->commit();
	echo $uniqueToken;
} catch (PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
