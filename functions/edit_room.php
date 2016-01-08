<?php
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();
$type = $_POST["type"];
$language = $_POST["language"];
$description = $_POST["description"];
$roomToken = $_POST["roomToken"];

try{
	$db->beginTransaction();
	$editRoom = $db->query("UPDATE rooms
								SET room_type = '$type', room_lang = '$language', room_description = '$description'
								WHERE room_token = '$roomToken'");
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
}
?>
