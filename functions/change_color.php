<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$userToken = $_POST["userToken"];
$color = strtoupper($_POST["color"]);
$change = $db->query("UPDATE user_preferences SET up_color='$color' WHERE up_user_id='$userToken'");
?>
