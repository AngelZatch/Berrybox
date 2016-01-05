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
?>
