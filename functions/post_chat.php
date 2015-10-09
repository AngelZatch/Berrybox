<?php
include "db_connect.php";
session_start();
$db = PDOFactory::getConnection();

$message = addslashes($_POST["message"]);
$token = $_POST["token"];
$author = $_SESSION["token"];
$destination = $_POST["destination"];
$scope = $_POST["scope"];
$time = date_create('now')->format('Y-m-d H:i:s');
if($message != ''){
	try{
		$db->beginTransaction();

		$upload = $db->prepare("INSERT INTO roomChat_$token(message_scope, message_author, message_destination, message_time, message_contents)
VALUES(:scope, :author, :destination, :time, :message)");
		$upload->bindParam(':scope', $scope);
		$upload->bindParam(':author', $author);
		$upload->bindParam(':destination', $destination);
		$upload->bindParam(':time', $time);
		$upload->bindParam(':message', $message);
		$upload->execute();
		$db->commit();
	}catch(PDOException $e){
		$db->rollBack();
	}
}
?>
