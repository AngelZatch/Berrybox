<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$index = $_POST["index"];

// We add one submission to the song_base
try{
$update = $db->query("UPDATE song_base SET submissions = submissions + 1
						WHERE song_base_id = '$index'");
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
