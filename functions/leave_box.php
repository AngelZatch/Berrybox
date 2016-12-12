<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_POST["box_token"];
$user_token = $_POST["user_token"];

$db->query("DELETE FROM roomUsers_$box_token WHERE room_user_token = '$user_token'");
?>
