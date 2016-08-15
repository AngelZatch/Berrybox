<?php
require_once "db_connect.php";
require_once "tools.php";
$db = PDOFactory::getConnection();

if(isset($_POST["table"])){ // AJAX call.
	$table_name = htmlspecialchars($_POST["table"]);
	parse_str($_POST["values"], $values);
	addEntry($db, $table_name, $values);
}

// Type hinting $values because it can come from AJAX or PHP and MUST be an array. This function has to do the minimal amount of work, it doesn't have time to play with the values argument, which has to be parsed or unserialized beforehand.
function addEntry($db, $table_name, Array $values){
	// Constructing generic query
	$query = "INSERT INTO $table_name(";
	foreach($values as $column => $value){
		$query .= "$column";
		if($column !== end(array_keys($values))){
			$query .= ", ";
		} else {
			$query .= ")";
		}
	}
	$query .= " VALUES(";
	foreach($values as $column => $value){
		if($column == "session_teacher" || $column == "event_handler" || $column == "booking_holder"){
			$value = solveAdherentToId($value);
		}
		if(preg_match("/(start|end|date)/i", $column)){
			// In the database, all dates contain one of these 3 words. We can then test against them to find dates and format them correctly.
			$value_date = DateTime::createFromFormat("d/m/Y H:i:s", $value);
			/*if($value_date == false)
				return $column;*/
			$value = $value_date->format("Y-m-d H:i:s");
		} else {
			$value = htmlspecialchars($value);
		}
		$query .= "'$value'";
		if($column !== end(array_keys($values))){
			$query .= ", ";
		} else {
			$query .= ")";
		}
	}

	try{
		$db->beginTransaction();
		$insert = $db->query($query);
		$new_id = $db->lastInsertId();
		$db->commit();
		if(isset($_POST["table"])) //AJAX
			echo $new_id;
		else //PHP
			return $new_id;
	} catch(PDOException $e){
		$db->rollBack();
		echo $e->getMessage();
	}
}
