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
?>
