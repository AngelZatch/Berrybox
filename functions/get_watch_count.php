<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["token"];

echo $chatNumber = $db->query("SELECT * FROM roomUsers_$roomToken WHERE room_user_present = 1")->rowCount();
?>
