<?php
session_start();
include "db_connect.php";
include "tools.php";

$db = PDOFactory::getConnection();

$box_token = $_POST["box_token"];
$user_target = $_POST["user_target"];

$user_token = solveUserFromName($db, $user_target);

// Changing creator in rooms
$db->query("UPDATE rooms SET room_creator = '$user_token' WHERE box_token = '$box_token'");

// Putting 1 as power for everyone not timeouted or banned.
$db->query("UPDATE roomUsers_$box_token SET room_user_state = 1 WHERE room_user_state BETWEEN 1 AND 3");

// Putting receiving user to 2 in power
$db->query("UPDATE roomUsers_$box_token SET room_user_state = 2 WHERE room_user_token = '$user_token'");

// Putting all mods to 3 in power
$db->query("UPDATE roomUsers_$box_token SET room_user_state = 3 WHERE room_user_token IN (SELECT moderator_token FROM user_moderators WHERE user_token = '$user_token')");
?>
