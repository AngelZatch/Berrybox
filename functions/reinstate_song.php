<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];
$id = $_POST["id"];

$ignore = $db->query("UPDATE roomHistory_$roomToken
					SET video_status = 0
					WHERE room_history_id = $id");

$name = $db->query("SELECT video_name
					FROM roomHistory_$roomToken
					WHERE room_history_id = $id")->fetch(PDO::FETCH_ASSOC);

echo $name["video_name"];
?>
