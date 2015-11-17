<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

/* The script has multiple goals
- Close the room to prevent access
- Alert users that the room will shut down for good
- Kick every curious user that stayed until the end of the timer
- Move the tables of the room to the archive of tables
*/

// To ensure all is done even if the creator leaves
ignore_user_abort(true);
set_time_limit(300);

$roomToken = $_POST["roomToken"];
$author = $_POST["userToken"];

// Close the room to prevent access
$closeRoom = $db->query("UPDATE rooms
						SET room_active = '0'
						WHERE room_token = '$roomToken'");

// Entering the loop displaying messages for 4 to 1 minute remaining
$scope = 4;
$type = 4;
for($i = 4; $i > 0; $i--){
	sleep(60);
	$time = date('Y-m-d H:i:s', time());
	$message = "{close_room_$i}";
	$upload = $db->prepare("INSERT INTO roomChat_$roomToken(message_scope, message_type, message_author, message_time, message_contents)
							VALUES(:scope, :type, :author, :time, :message)");
	$upload->bindParam(':scope', $scope);
	$upload->bindParam(':type', $type);
	$upload->bindParam(':author', $author);
	$upload->bindParam(':time', $time);
	$upload->bindParam(':message', $message);
	$upload->execute();
}

// Displaying the 30 seconds remaining
sleep(30);
$time = date('Y-m-d H:i:s', time());
$message = "{close_room_0.5}";
$upload = $db->prepare("INSERT INTO roomChat_$roomToken(message_scope, message_type, message_author, message_time, message_contents)
VALUES(:scope, :type, :author, :time, :message)");
$upload->bindParam(':scope', $scope);
$upload->bindParam(':type', $type);
$upload->bindParam(':author', $author);
$upload->bindParam(':time', $time);
$upload->bindParam(':message', $message);
$upload->execute();

sleep(30);

// Kick every remaining user in the room after 5 mins
$kickUsers = $db->query("UPDATE roomUsers_$roomToken
						SET room_user_present = '0'
						WHERE room_user_present = '1'");
?>
