<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$user_token = $_POST["user_token"];
$color = strtoupper($_POST["color"]);
$change = $db->query("UPDATE user_preferences SET up_color='$color' WHERE user_token='$user_token'");
?>
