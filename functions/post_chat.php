<?php
include "db_connect.php";
session_start();
$db = PDOFactory::getConnection();

$message = addslashes($_POST["message"]);
$token = $_POST["token"];
$author = $_SESSION["token"];
$time = date_create('now')->format('Y-m-d H:i:s');

try{
	$db->beginTransaction();
	$upload = $db->prepare("INSERT INTO roomChat_$token(message_author, message_time, message_contents)
VALUES(:author, :time, :message)");
	$upload->bindParam(':author', $author);
	$upload->bindParam(':time', $time);
	$upload->bindParam(':message', $message);
	$upload->execute();
	$db->commit();
}catch(PDOException $e){
	$db->rollBack();
}
?>
