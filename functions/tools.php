<?php
require_once "db_connect.php";

function generateReference($len){
	// Creation of the unique token
	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$chars_length = strlen($characters);
	$reference = '';
	for ($i = 0; $i < $len; $i++) {
		$reference .= $characters[rand(0, $chars_length - 1)];
	}
	// Check for potential duplicates (only for user tokens, which are 6-char long)
	if($len == 6){
		checkDuplicate($reference, $len);
	}
	return $reference;
}

function checkDuplicate($token, $len){
	$db = PDOFactory::getConnection();
	$search = $db->query("SELECT * FROM user WHERE user_token='$token'");
	if($search->rowCount() != 0){
		$reference = generateReference($len);
	} else {
		return $reference;
	}
}

function getPlaylistOrdering($db, $box_token){
	// First, we get the index of the previous entry
	$current_playlist_index = $db->query("SELECT playlist_order FROM roomHistory_$box_token ORDER BY playlist_order DESC LIMIT 1")->fetch(PDO::FETCH_COLUMN);
	$current_playlist_index = ($current_playlist_index != NULL)?$current_playlist_index:1;
	$current_playlist_index++;
	return $current_playlist_index;
}

function solveUserFromName($db, $user_name){
	return $db->query("SELECT user_token FROM user WHERE user_pseudo = '$user_name'")->fetch(PDO::FETCH_COLUMN);
}

function refreshBoxActivity($db, $box_token, $user_token){
	// This function is called everytime the box can be "reactivated". A box can only be done by its creator or mod team.
	$authorized = $db->query("SELECT room_user_token FROM roomUsers_$box_token WHERE room_user_state = 2 OR room_user_state = 3")->fetchAll(PDO::FETCH_COLUMN);
	if(in_array($user_token, $authorized)){
		date_default_timezone_set('UTC');
		$time = date('Y-m-d H:i:s', time());
		$db->query("UPDATE rooms SET room_active = 1, last_active_date = '$time' WHERE box_token = '$box_token'");
	}
}
?>
