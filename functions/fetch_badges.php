<?php
session_start();
require_once "db_connect.php";
require_once "tools.php";
$lang = (isset($_SESSION["user_lang"]))?$_SESSION["user_lang"]:"en";
include "../languages/lang.".$lang.".php";
$db = PDOFactory::getConnection();

$user_token = (isset($_SESSION["token"]))?$_SESSION["token"]:-1;
$target_token = solveUserFromName($db, $_GET["target_token"]);

if($user_token == $target_token){
	$query = "SELECT b.badge_id, badge_name, badge_icon, unlock_date, featured FROM badges b
				LEFT JOIN user_badges ub ON (b.badge_id = ub.badge_id AND ub.user_token = '$user_token')";
} else {
	$query = "SELECT b.badge_id, badge_name, badge_icon, ub.unlock_date, ub.featured FROM user_badges ub
					JOIN user u ON ub.user_token = u.user_token
					JOIN badges b ON ub.badge_id = b.badge_id
					WHERE ub.user_token = '$target_token'";
}
$stmt = $db->query($query);

$badges = array();
while($badge = $stmt->fetch()){
	$b = array(
		"id" => $badge["badge_id"],
		"name" => $lang[$badge["badge_name"]],
		"description" => $lang[$badge["badge_name"]."_description"],
		"featured" => $badge["featured"]
	);

	if(isset($badge["unlock_date"])){
		$b["unlock_date"] = $badge["unlock_date"];
		$b["icon"] = "assets/badges/".$badge["badge_icon"].".png";
		$b["status"] = "";
	} else {
		$b["icon"] = "assets/badges/".$badge["badge_icon"]."_locked.png";
		$b["status"] = $lang["locked"];
	}
	array_push($badges, $b);
}
echo json_encode($badges);

?>
