<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];

$checkCreator = $db->query("SELECT room_user_present
						FROM roomUsers_$roomToken
						WHERE room_user_state = '2'")->fetch(PDO::FETCH_ASSOC);

echo $checkCreator["room_user_present"];
?>
