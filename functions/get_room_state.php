<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];

$status = $db->query("SELECT room_active
					FROM rooms
					WHERE room_token = '$roomToken'")->fetch(PDO::FETCH_ASSOC);

echo $status["room_active"];
?>
