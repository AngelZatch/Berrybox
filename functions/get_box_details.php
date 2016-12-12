<?php
session_start();
include "db_connect.php";
$db = PDOFactory::getConnection();

$box_token = $_GET["box_token"];

$box_status = $db->query("SELECT room_name, box_token, room_active, room_creator, room_play_type, room_submission_rights, user_pseudo, stat_visitors, stat_followers, user_pp, room_protection, room_description
						FROM rooms r
						JOIN user u ON r.room_creator = u.user_token
						JOIN user_stats us ON r.room_creator = us.user_token
						WHERE box_token = '$box_token'")->fetch();

if(isset($_SESSION["username"]) && $box_status["user_pseudo"] != $_SESSION["username"]){
	$userFollow = $db->query("SELECT * FROM user_follow uf
								WHERE user_following = '$_SESSION[token]'
								AND user_followed = '$box_status[room_creator]'")->rowCount();
	$box_status["following_creator"] = $userFollow;
}

echo json_encode($box_status);
?>
