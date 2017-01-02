<?php
session_start();
require_once "db_connect.php";
require_once "tools.php";
$db = PDOFactory::getConnection();

// We get the userID from Facebook.
$facebook_token = $_POST["facebook_token"];
$access_token = $_POST["access_token"];
$user_pseudo = "u-".$facebook_token;
if(isset($_POST["mail"])){
	$user_mail = $_POST["mail"];
	$match = $db->query("SELECT user_token, user_pseudo, user_power, user_lang FROM user WHERE facebook_token = '$facebook_token' OR user_mail = '$user_mail'")->fetch();
} else {
	$user_mail = NULL;
	$match = $db->query("SELECT user_token, user_pseudo, user_power, user_lang FROM user WHERE facebook_token = '$facebook_token'")->fetch();
}

if($match["user_token"] != null){
	$db->query("UPDATE user SET facebook_token = '$facebook_token', faccess_token = '$access_token' WHERE user_token = '$match[user_token]'");
	// We log the user in
	$_SESSION["username"] = $match["user_pseudo"];
	$_SESSION["power"] = $match["user_power"];
	$_SESSION["token"] = $match["user_token"];
	$_SESSION["user_lang"] = $match["user_lang"];
} else {
	// We sign the user in
	// Token
	$user_token = generateReference(6);
	// Color
	$color_id = rand(1,20);
	$color = $db->query("SELECT color_value FROM name_colors WHERE number = $color_id")->fetch();

	$new_user = $db->query("INSERT INTO user(user_token, user_pseudo, facebook_token, faccess_token, user_mail) VALUES('$user_token', '$user_pseudo', '$facebook_token', '$access_token', '$user_mail')");

	$new_user_preferences = $db->query("INSERT INTO user_preferences(user_token, up_color) VALUES('$user_token', '$color[color_value]')");

	$new_user_stats = $db->query("INSERT INTO user_stats(user_token) VALUES('$user_token')");

	$_SESSION["username"] = $user_pseudo;
	$_SESSION["power"] = "0";
	$_SESSION["token"] = $user_token;
	$_SESSION["user_lang"] = "en";
}
?>
