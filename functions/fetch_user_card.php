<?php
include "db_connect.php";
session_start();
$db = PDOFactory::getConnection();

$user = $_POST["user"];

$details = $db->query("SELECT * FROM user u
					JOIN user_stats us ON u.user_token = us.user_token
					WHERE u.user_pseudo = '$user'")->fetch(PDO::FETCH_ASSOC);

$following = $db->query("SELECT * FROM user_follow WHERE user_following = '$_SESSION[token]' AND user_followed = '$details[user_token]'");

$d = array();
$d["user_pseudo"] = $user;
$d["user_pp"] = "profile-pictures/".$details["user_pp"];
$d["rooms"] = $details["stat_rooms_created"];
$d["songs"] = $details["stat_songs_submitted"];
$d["visitors"] = $details["stat_visitors"];
$d["followers"] = $details["stat_followers"];
if($following->rowCount() != 1){
	$d["following"] = '0';
} else {
	$d["following"] = '1';
}

echo json_encode($d);
?>
