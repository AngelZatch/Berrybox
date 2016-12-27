<?php
session_start();
include "db_connect.php";
$db = PDOFactory::getConnection();

$user_token = $_GET["user_token"];

$user_settings = $db->query("SELECT badge_alert FROM user_preferences WHERE user_token = '$user_token'")->fetch();

echo json_encode($user_settings);
?>
