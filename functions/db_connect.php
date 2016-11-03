<?php
class PDOFactory{
	public static function getConnection(){
		$db = new PDO('mysql:host=127.0.0.1;dbname=Strawberry;charset=utf8', 'root', 'FvOm9pLn8m');
		$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		$db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
		return $db;
	}
}
?>
