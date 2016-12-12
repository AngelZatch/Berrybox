<?php
session_start();
require_once "db_connect.php";
include "tools.php";
$db = PDOFactory::getConnection();

$message = addslashes($_POST["message"]);
$token = $_POST["token"];
$author = $_SESSION["token"];
$scope = $_POST["scope"];
$type = $_POST["type"];
$user_lang = $_SESSION["user_lang"];

if(isset($_POST["solveDestination"])){
	$destination = solveUserFromName($db, $_POST["solveDestination"]);
} else {
	$destination = $_POST["destination"];
}

date_default_timezone_set('UTC');
$time = date('Y-m-d H:i:s', time());
if($message != ''){
	try{
		//$db->beginTransaction();

		// DATABASE
		$upload = $db->prepare("INSERT INTO roomChat_$token(message_scope, message_type, message_author, message_destination, message_time, message_contents)
VALUES(:scope, :type, :author, :destination, :time, :message)");
		$upload->bindParam(':scope', $scope);
		$upload->bindParam(':type', $type);
		$upload->bindParam(':author', $author);
		$upload->bindParam(':destination', $destination);
		$upload->bindParam(':time', $time);
		$upload->bindParam(':message', $message);
		$upload->execute();

		// Update last active date
		refreshBoxActivity($db, $token, $author);

		//$db->commit();

		// SOCKET
		$author_details = $db->query("SELECT user_pseudo, up_color, user_power, room_user_state FROM user u
									JOIN user_preferences up ON u.user_token = up.up_user_id
									JOIN roomusers_$token ru ON u.user_token = ru.room_user_token
									WHERE u.user_token = '$author'")->fetch();
		$author_badge = $db->query("SELECT badge_icon FROM user_badges ub JOIN badges b ON ub.badge_id = b.badge_id WHERE user_token = '$author' AND featured = '1'")->fetch(PDO::FETCH_COLUMN);
		$message_data = array(
			"packet_type" => "chat",
			"token" => $token,
			"scope" => $scope,
			"subType" => $type,
			"author" => $author_details["user_pseudo"],
			"authorToken" => $author,
			"authorColor" => $author_details["up_color"],
			"authorStatus" => $author_details["room_user_state"],
			"authorGlobalPower" => $author_details["user_power"],
			"timestamp" => $time,
			"featured_badge" => $author_badge,
			"content" => $message
		);
		if($destination != ""){
			$destination_details = $db->query("SELECT user_pseudo, up_color FROM user u
												JOIN user_preferences up ON u.user_token = up.up_user_id
												WHERE u.user_token = '$destination'")->fetch();
			$message_data["destination"] = $destination_details["user_pseudo"];
			$message_data["destinationColor"] = $destination_details["up_color"];
			$message_data["destinationToken"] = $destination;
		}
		$contentRaw = $message;
		$pattern = "#\{(.*?)\}#";
		if(preg_match($pattern, $contentRaw, $matches)){
			$translationToken = $db->query("SELECT expression_$user_lang FROM translation
							WHERE shortened_token = '$matches[1]'
							OR expression_en = '$matches[1]'
							OR expression_fr = '$matches[1]'
							OR expression_jp = '$matches[1]'")->fetch();
			$messageInterpreted = preg_replace($pattern, $translationToken["expression_$user_lang"], $contentRaw);
			$message_data["content"] = stripslashes($messageInterpreted);
		} else {
			$message_data["content"] = stripslashes($message);
		}

		// Pushing
		$context = new ZMQContext();
		$socket = $context->getSocket(ZMQ::SOCKET_PUSH, 'my pusher');
		$socket->connect("tcp://localhost:5555");
		$socket->send(json_encode($message_data));
	}catch(PDOException $e){
		$db->rollBack();
	}
}
?>
