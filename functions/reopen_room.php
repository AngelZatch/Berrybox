<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
$roomToken = $_POST["roomToken"];

$reopen = $db->query("UPDATE rooms
					SET room_active = '1'
					WHERE box_token='$roomToken'")->fetch(PDO::FETCH_ASSOC);
?>
