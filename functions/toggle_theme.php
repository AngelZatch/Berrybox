<?php
include "db_connect.php";
$db = PDOFactory::getConnection();
$user_token = $_POST["user_token"];
$state = $_POST["state"];
try{
	if($state == '1'){
		$value = 0;
	} else {
		$value = 1;
	}
	$toggle = $db->query("UPDATE user_preferences
							SET up_theme = $value WHERE up_user_id = '$user_token'");
	echo $value;
} catch (PDOException $e){
	echo "2"; // Error code
}

?>
