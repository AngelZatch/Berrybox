<?php
include "db_connect.php";
session_start();
$db = PDOFactory::getConnection();

$link = $_POST["url"];
$time = date_create('now')->format('Y-m-d H:i:s');
$user = $_SESSION["token"];
$roomToken = $_POST["roomToken"];

try{
	$db->beginTransaction();
	$upload = $db->prepare("INSERT INTO roomHistory_$roomToken(history_link, history_time, history_user)
	VALUES(:link, :time, :user)");
	$upload->bindParam(':link', $link);
	$upload->bindParam(':time', $time);
	$upload->bindParam(':user', $user);
	$upload->execute();
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
