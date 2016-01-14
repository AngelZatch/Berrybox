<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
$userFollowing = $_POST["userFollowing"];
$userFollowed = $_POST["userFollowed"];

try{
	$db->beginTransaction();
	$follow = $db->prepare("DELETE FROM user_follow WHERE user_following = :follower AND user_followed = :followed");
	$follow->bindParam(':follower', $userFollowing);
	$follow->bindParam(':followed', $userFollowed);
	$follow->execute();

	$updateFollowCount = $db->query("UPDATE user_stats SET stat_followers = stat_followers - 1 WHERE user_token = '$userFollowed'");
	$db->commit();
	echo "1"; // Success code
} catch (PDOException $e){
	$db->rollBack();
	echo "2"; // Error code
}
?>
