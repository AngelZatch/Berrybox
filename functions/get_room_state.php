<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];

$status = $db->query("SELECT room_active, room_submission_rights, room_play_type
					FROM rooms
					WHERE room_token = '$roomToken'")->fetch(PDO::FETCH_ASSOC);

echo json_encode($status);
?>
