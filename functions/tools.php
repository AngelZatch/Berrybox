<?php
function generateUserToken() {
	$length = 6;
	$characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$chars_length = strlen($characters);
	$reference = '';
	for ($i = 0; $i < $length; $i++) {
		$reference .= $characters[rand(0, $chars_length - 1)];
	}
	return $reference;
}
?>
