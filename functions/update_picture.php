<?php
session_start();
require_once 'db_connect.php';
$db = PDOFactory::getConnection();

$data = $_POST['picture_value'];
$user_token = $_POST["user_token"];
$picture_type = $_POST["picture_type"];

list($type, $data) = explode(';', $data);
list(, $data)      = explode(',', $data);
$data = base64_decode($data);

// Target directory to move the picture
if($picture_type == "picture")
	$target_dir = "../profile-pictures/";
else
	$target_dir = "../profile-banners/";

// File name
$file_name = $user_token.'-'.time();

// Real file
$new_file = $target_dir.$file_name.'.png';

// Fictional file
$fictional_file = $file_name.'.png';

file_put_contents($new_file, $data);
move_uploaded_file($new_file, $fictional_file);
$query = "UPDATE user SET";
if($picture_type == "picture")
	$query .= " user_pp ";
else
	$query .= " user_banner ";
$query .= "= '$fictional_file' WHERE user_token = '$user_token'";

/*echo $query;*/
$update = $db->query($query);
echo $fictional_file;
?>
