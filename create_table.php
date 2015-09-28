<?php
include "functions/db_connect.php";
$db = PDOFactory::getConnection();

/*
* Used to create a user table containing the whole library of a user.
*/
$root = "user_";
$seed = "DBUEDG01AD";

$token = $root.$seed;

$table = "CREATE TABLE $token (
track_id INT(100) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
track_name VARCHAR(200),
track_artist VARCHAR(100)
)";

$db->exec($table);
