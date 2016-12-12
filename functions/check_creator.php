<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_POST["box_token"];
date_default_timezone_set('UTC');
$time = date('Y-m-d H:i:s', time() - 15);

$checkCreator = $db->query("SELECT COUNT(room_user_token) AS count
						FROM roomUsers_$box_token
						WHERE room_user_state = '2'")->fetch(PDO::FETCH_ASSOC);

echo $checkCreator["count"];
?>
