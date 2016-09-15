<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_POST["box_token"];

$reopen = $db->query("UPDATE rooms
					SET room_active = '1'
					WHERE box_token='$box_token'");
?>
