<?php
session_start();

$session_details = array (
	"token" => $_SESSION["token"],
	"username" => $_SESSION["username"],
	"power" => $_SESSION["power"],
	"lang" => $_SESSION["user_lang"]
);
echo json_encode($session_details);
?>
