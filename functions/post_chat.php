<?php
include "db_connect.php";
session_start();
$db = PDOFactory::getConnection();

$message = addslashes($_POST["message"]);
$token = $_POST["token"];
$author = $_SESSION["token"];
$scope = $_POST["scope"];
$type = $_POST["type"];

if(isset($_POST["solveDestination"])){
	$solve = $db->query("SELECT user_token FROM user WHERE user_pseudo='$_POST[solveDestination]'")->fetch(PDO::FETCH_ASSOC);
	$destination = $solve["user_token"];
} else {
	$destination = $_POST["destination"];
}

date_default_timezone_set('UTC');
$time = date('Y-m-d H:i:s', time());
if($message != ''){
	try{
		$db->beginTransaction();

		$upload = $db->prepare("INSERT INTO roomChat_$token(message_scope, message_type, message_author, message_destination, message_time, message_contents)
VALUES(:scope, :type, :author, :destination, :time, :message)");
		$upload->bindParam(':scope', $scope);
		$upload->bindParam(':type', $type);
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
