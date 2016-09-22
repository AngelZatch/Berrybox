<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$history_id = $_POST["history_id"];
$current_order = $_POST["current_order"];
$action = $_POST["action"]; // (up, down)
$box_token = $_POST["box_token"];

if($action == "up")
	$next_order = $current_order + 1;
if($action == "down")
	$next_order = $current_order - 1;

$swap_other = $db->query("UPDATE roomHistory_$box_token SET playlist_order = $current_order WHERE playlist_order = $next_order");

$swap = $db->query("UPDATE roomHistory_$box_token SET playlist_order = $next_order WHERE room_history_id = $history_id");
?>
