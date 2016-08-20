<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$user_token = $_POST["user_token"];
$color = strtoupper($_POST["color"]);
$change = $db->query("UPDATE user_preferences SET up_color='$color' WHERE up_user_id='$user_token'");
?>
