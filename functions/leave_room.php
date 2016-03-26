<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];
$userToken = $_POST["userToken"];
$timestamp = date_create('now')->format('Y-m-d H:i:s');
$state = 1;

$leave = $db->query("UPDATE roomUsers_$roomToken SET room_user_present=0 WHERE room_user_token='$userToken'");
?>
