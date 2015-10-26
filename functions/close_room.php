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

$roomToken = $_POST["roomToken"];

// Close the room to prevent access
$closeRoom = $db->query("UPDATE rooms
						SET room_active = '0'
						WHERE room_token = '$roomToken'");

// Sleep 5 minutes before closing the room
sleep(300);

// Kick every remaining user in the room after 5 mins
$kickUsers = $db->query("UPDATE roomUsers_$roomToken
						SET room_user_present = '0'
						WHERE room_user_present = '1'");

// Sleep 3 minutes to allow every user to be kicked out
//sleep(180);
?>
