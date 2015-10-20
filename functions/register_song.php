<?php
include "db_connect.php";
$db = PDOFactory::getConnection();

$id = $_POST["id"];

// We register the song into the song_base table for stats
try{
$update = $db->query("INSERT INTO song_base(link, submissions)
						VALUES ('$id', 1)
						ON DUPLICATE KEY UPDATE
						submissions = submissions + 1");
	echo $id;
} catch(PDOException $e){
	echo $e->getMessage();
}
?>
