<?php
session_start();
include "db_connect.php";
include "tools.php";

$db = PDOFactory::getConnection();

$box_token = $_POST["box_token"];
$user_target = $_POST["user_target"];

$user_token = solveUserFromName($db, $user_target);

$db->query("UPDATE rooms SET room_creator = '$user_token' WHERE box_token = '$box_token'");

$db->query("UPDATE roomUsers_$box_token SET room_user_state = 1 WHERE room_user_token = '$_SESSION[token]'");

$db->query("UPDATE roomUsers_$box_token SET room_user_state = 2 WHERE room_user_token = '$user_token'");
?>
