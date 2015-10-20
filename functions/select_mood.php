<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$mood = $_POST["mood"];
$id = $_POST["id"];

$song = $db->query("UPDATE song_base
					SET emotion_$mood = emotion_$mood + 1
					WHERE link = '$id'");
?>
