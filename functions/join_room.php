<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];
$userToken = $_POST["userToken"];
$timestamp = date_create('now')->format('Y-m-d H:i:s');
$state = 1;

$join = $db->prepare("INSERT INTO roomUsers_$roomToken(room_user_token, room_user_state, room_user_date_state)
VALUES(':token', ':state', :'date')");
$db->bindParam(':token', $userToken);
$db->bindParam(':state', $state);
$db->bindParam(':date', $timestamp);
?>
