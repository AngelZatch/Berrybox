<?php
session_start();
if(isset($_POST["login"])){
	$db = PDOFactory::getConnection();

	$username = $_POST["login_name"];
	$password = $_POST["login_pwd"];

	$checkCredentials = $db->prepare("SELECT * FROM user WHERE user_pseudo=? AND user_pwd=?");
	$checkCredentials->bindParam(1, $username);
	$checkCredentials->bindParam(2, $password);
	$checkCredentials->execute();

	if($checkCredentials->rowCount() == 1){
		$credentials = $checkCredentials->fetch(PDO::FETCH_ASSOC);
		$_SESSION["username"] = $credentials["user_pseudo"];
		$_SESSION["power"] = $credentials["user_power"];
		$_SESSION["token"] = $credentials["user_token"];
		header("Location: home.php?lang=".$_GET["lang"]);
	}
}
?>
