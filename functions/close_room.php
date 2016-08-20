<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

// To ensure all is done even if the creator leaves
ignore_user_abort(true);

$roomToken = $_POST["roomToken"];
$author = $_POST["user_token"];

// Close the room to prevent access
$closeRoom = $db->query("UPDATE rooms
						SET room_active = '0'
						WHERE box_token = '$roomToken'");
?>
