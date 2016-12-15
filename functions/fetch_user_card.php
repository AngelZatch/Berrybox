<?php
include "db_connect.php";
session_start();
$lang = (isset($_SESSION["user_lang"]))?$_SESSION["user_lang"]:"en";
include "../languages/lang.".$lang.".php";
$db = PDOFactory::getConnection();

$user = $_GET["user_pseudo"];

$details = $db->query("SELECT * FROM user u
					JOIN user_stats us ON u.user_token = us.user_token
					WHERE u.user_pseudo = '$user'")->fetch();
$badge = $db->query("SELECT badge_icon, badge_name, unlock_date FROM user_badges ub
					JOIN badges b ON ub.badge_id = b.badge_id
					WHERE user_token = (SELECT user_token FROM user u WHERE user_pseudo = '$user') AND featured = '1'")->fetch();

$d = array(
"user_pseudo" => $user,
	"user_pp" => "profile-pictures/".$details["user_pp"],
	"rooms" => $details["stat_rooms_created"],
	"songs" => $details["stat_songs_submitted"],
	"visitors" => $details["stat_visitors"],
	"followers" => $details["stat_followers"],
);

if($badge != null){
	$d["badge"] = "assets/badges/".$badge["badge_icon"].".png";
	$d["badge_name"] = $lang[$badge["badge_name"]];
	$d["unlock_date"] = $badge["unlock_date"];
}

if(isset($_SESSION["token"])){
	$following = $db->query("SELECT * FROM user_follow WHERE user_following = '$_SESSION[token]' AND user_followed = '$details[user_token]'");
	if($following->rowCount() != 1){
		$d["following"] = '0';
	} else {
		$d["following"] = '1';
	}
}

echo json_encode($d);
?>
