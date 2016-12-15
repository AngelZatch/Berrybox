<?php
session_start();
require_once "db_connect.php";
$db = PDOFactory::getConnection();
$user_token = $_SESSION["token"];

$badge_id = $_POST["badge_id"];

$target_badge_status = $db->query("SELECT featured FROM user_badges WHERE user_token = '$user_token' AND badge_id = $badge_id")->fetch(PDO::FETCH_COLUMN);

if($target_badge_status != null){
	if($target_badge_status == 0){
		// If the featured status was 0, then we put it as featured.
		$db->query("UPDATE user_badges
				SET featured =
					CASE
						WHEN badge_id = $badge_id AND user_token = '$user_token' THEN 1
						WHEN badge_id != $badge_id AND user_token = '$user_token' THEN 0
						ELSE featured
					END");
		echo $badge_id;
	} else {
		// Else, we "unfeature" it.
		$db->query("UPDATE user_badges SET featured = 0 WHERE badge_id = $badge_id AND user_token = '$user_token'");
		echo null;
	}
} else {
	echo -1;
}

?>
