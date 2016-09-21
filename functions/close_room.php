<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_POST["box_token"];

// Close the room to prevent access
$closeRoom = $db->query("UPDATE rooms
						SET room_active = '0'
						WHERE box_token = '$box_token'");
?>
