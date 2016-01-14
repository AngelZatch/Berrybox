<?php
include "db_connect.php";
session_start();
$db = PDOFactory::getConnection();

$index = $_POST["index"];
$name = addslashes($_POST["name"]);

$update = $db->query("UPDATE song_base SET video_name = '$name' WHERE song_base_id = '$index'");
?>
