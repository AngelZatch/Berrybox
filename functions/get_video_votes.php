<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$video_id = $_GET["index"];

$moods = $db->query("SELECT vote_mood, COUNT(vote_mood) AS count_mood FROM `votes` WHERE video_index = $video_id GROUP BY vote_mood");

echo json_encode($moods->fetchAll(PDO::FETCH_ASSOC));
?>
