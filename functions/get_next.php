<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$roomToken = $_POST["roomToken"];
$prev = $_POST["lastPlayed"];

$next = $db->query("SELECT history_link, video_name FROM roomHistory_$roomToken
					WHERE room_history_id > (SELECT MAX(room_history_id) AS most_recent_id
					FROM roomHistory_$roomToken
					WHERE history_link = '$prev')
					ORDER BY room_history_id ASC
					LIMIT 1")->fetch(PDO::FETCH_ASSOC);
$n = array();
$n["link"] = $next["history_link"];
$n["title"] = $next["video_name"];
echo json_encode($n);
?>
