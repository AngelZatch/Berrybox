<?php
session_start();
include "db_connect.php";
$db = PDOFactory::getConnection();

// We get the user lang setting
if(isset($_SESSION["token"])){
	$user_lang = $db->query("SELECT user_lang FROM user u WHERE user_token = '$_SESSION[token]'")->fetch(PDO::FETCH_COLUMN);
} else {
	$user_lang = "en";
}

include "../languages/lang.$user_lang.php";
$lang["user_lang"] = $user_lang;
echo json_encode($lang);

?>
