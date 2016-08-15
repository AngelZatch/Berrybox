<?php
session_start();
include "db_connect.php";
$db = PDOFactory::getConnection();

if(isset($_SESSION["token"])){
	$user_lang = $db->query("SELECT user_lang FROM user u WHERE user_token = '$_SESSION[token]'")->fetch(PDO::FETCH_COLUMN);
} else {
	$user_lang = "en";
}

echo $user_lang;

?>
