<?php
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();
$type = $_POST["type"];
$language = $_POST["language"];
$protect = $_POST["protect"];
$description = $_POST["description"];
$roomToken = $_POST["roomToken"];

try{
	$db->beginTransaction();
	$oldProtectValue = $db->query("SELECT room_protection FROM rooms WHERE room_token = '$roomToken'")->fetch(PDO::FETCH_ASSOC);
	$editRoom = $db->query("UPDATE rooms
								SET room_type = '$type', room_lang = '$language', room_protection = '$protect', room_description = '$description'
								WHERE room_token = '$roomToken'");
	$db->commit();
	echo $oldProtectValue["room_protection"];
} catch(PDOException $e){
	$db->rollBack();
}
?>
