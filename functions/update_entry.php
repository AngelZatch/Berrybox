<?php
// This is the generic script to edit an entry into whatever table. With this, you only have to call it once you're set and avoid a billion scripts for every little thing.
require_once "db_connect.php";
require_once "tools.php";
$db = PDOFactory::getConnection();

// Array of values (user serialize in php to have the correct format)
parse_str($_POST["values"], $values);

// The table and entry of it we'll update
$table_name = htmlspecialchars($_POST["table"]);
$entry_id = htmlspecialchars($_POST["target_id"]);

// We get the name of the primary key
$primary_key = $db->query("SHOW INDEX FROM $table_name WHERE Key_name = 'PRIMARY'")->fetch(PDO::FETCH_ASSOC);

// Construction of the query
$query = "UPDATE $table_name SET ";
foreach($values as $column => $value){
	// Have to solve users to their ID if needed here.
	if(preg_match("/(start|end|date)/i", $column)){
		// In the database, all dates contain one of these 3 words. We can then test against them to find dates and format them correctly.
		if($value != null){
			$value_date = DateTime::createFromFormat("d/m/Y H:i:s", $value);
			$value = $value_date->format("Y-m-d H:i:s");
		} else {
			$value = NULL;
		}
	} else {
		$value = htmlspecialchars($value);
	}
	if($value != NULL)
		$query .= "$column = '$value'";
	else
		$query .= "$column = NULL";
	if($column !== end(array_keys($values))){
		$query .= ", ";
	}
}
$query .= " WHERE ";

if(strlen($entry_id) == 6)
	$query .= "user_token";
else if(strlen($entry_id) == 15)
	$query .= "box_token";
else
	$query .= $primary_key["Column_name"];


$query .= " = '$entry_id'";

// Execution
try{
	$db->beginTransaction();
	$update = $db->query($query);
	echo $query;
	$db->commit();
} catch(PDOException $e){
	$db->rollBack();
	echo $e->getMessage();
}
?>
