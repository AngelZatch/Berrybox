<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$title = $_POST["title"];
$roomToken = $_POST["roomToken"];

$edit = $db->query("UPDATE rooms
						SET room_name = '$title'
						WHERE room_token = '$roomToken'");
?>
