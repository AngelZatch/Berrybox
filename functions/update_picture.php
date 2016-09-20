<?php
session_start();
require_once 'db_connect.php';
$db = PDOFactory::getConnection();

$data = $_POST['picture_value'];
$user_token = $_POST["user_token"];

list($type, $data) = explode(';', $data);
list(, $data)      = explode(',', $data);
$data = base64_decode($data);

// Target directory to move the picture
$target_dir = "../profile-pictures/";

// Real file
$new_file = $target_dir.$user_token.'.png';

// Fictional file
$fictional_file = $user_token.'.png';

file_put_contents($new_file, $data);
move_uploaded_file($new_file, $fictional_file);
$query = "UPDATE user SET user_pp = '$fictional_file' WHERE user_token = '$user_token'";

echo $query;
$update = $db->query($query);

if($_SESSION["token"] == $user_token)
	$_SESSION["photo"] = $fictional_file;
echo $fictional_file;
?>
