<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
$userFollowing = $_POST["userFollowing"];
$userFollowed = $_POST["userFollowed"];

$solveToken = $db->query("SELECT user_token FROM user u WHERE user_pseudo = '$userFollowed'")->fetch(PDO::FETCH_ASSOC);

try{
	$db->beginTransaction();
	$follow = $db->prepare("INSERT INTO user_follow(user_following, user_followed) VALUES(:follower, :followed)");
	$follow->bindParam(':follower', $userFollowing);
	$follow->bindParam(':followed', $solveToken["user_token"]);
	$follow->execute();

	$updateFollowCount = $db->prepare("UPDATE user_stats
									SET stat_followers = stat_followers + 1
									WHERE user_token = :user_followed");
	$updateFollowCount->bindParam(':user_followed', $solveToken["user_token"]);
	$updateFollowCount->execute();
	$db->commit();
	echo "1"; // Success code
} catch (PDOException $e){
	$db->rollBack();
	echo "2"; // Error code
}
?>
