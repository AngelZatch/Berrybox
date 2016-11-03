<?php
include "db_connect.php";
session_start();
$db = PDOFactory::getConnection();

$box_token = $_GET["box_token"];

$queue = $db->query("SELECT room_history_id FROM roomHistory_$box_token WHERE video_status = 0 ORDER BY room_history_id ASC")->fetchAll();
$order = $db->query("SELECT playlist_order FROM roomHistory_$box_token WHERE video_status = 0 ORDER BY room_history_id ASC")->fetchAll(PDO::FETCH_COLUMN);

$numbers = range(min($order), max($order));
shuffle($numbers);

$i = 0;
foreach($numbers as $number){
	$current_id = $queue[$i]["room_history_id"];
	try{
		$db->query("UPDATE roomHistory_$box_token SET playlist_order = $number WHERE room_history_id = $current_id");
	}catch(PDOException $e){
		echo $e->getMessage();
	}
	$i++;
}

?>
