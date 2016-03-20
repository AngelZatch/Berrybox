<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

// Demotes the selected user from the status of moderator relative to the administrator.
$userToken = $_POST["userToken"];
$adminToken = $_POST["adminToken"];
$roomToken = $_POST["roomToken"];

// First, we delete the entry in the moderators table
$promotion = $db->query("DELETE FROM user_moderators
							WHERE user_token = '$adminToken'
							AND moderator_token = '$userToken'");

// Then we update the status of the user in the room
$update = $db->query("UPDATE roomUsers_$roomToken
					SET room_user_state = 1
					WHERE room_user_token = '$userToken'");

$name = $db->query("SELECT user_pseudo FROM user WHERE user_token = '$userToken'")->fetch(PDO::FETCH_ASSOC);
echo $name["user_pseudo"];

// Same as for the promotion, the demotion is updated right now for this room and will be updated when the user joins a room
?>
