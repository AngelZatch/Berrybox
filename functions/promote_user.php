<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

// Promotes the selected user to the status of moderator relative to the administrator. Which means the new moderator will have his powers of moderation on every room created by that specific admin.
$targetToken = $_POST["targetToken"];
$userToken = $_POST["userToken"];
$roomToken = $_POST["roomToken"];

// First, we fill the user_moderators table to add the user to the the moderation list of the admin.
$promotion = $db->prepare("INSERT INTO user_moderators(user_token, moderator_token)
						VALUES(:admin, :mod)");
$promotion->bindParam(':admin', $userToken);
$promotion->bindParam(':mod', $targetToken);
$promotion->execute();

// Then we update the status of the user in the room
$update = $db->query("UPDATE roomUsers_$roomToken
					SET room_user_state = 3
					WHERE room_user_token = '$targetToken'");

$name = $db->query("SELECT user_pseudo FROM user WHERE user_token = '$targetToken'")->fetch(PDO::FETCH_ASSOC);
echo $name["user_pseudo"];

// The update of power is done when joining a room. It is easier for now to make the change here directly. The change will be picked up when loading other rooms by this creator.
?>
