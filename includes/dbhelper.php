<?php

function connectDatabase() {
	$db = mysql_connect('localhost', 'root', 'root');
	//$db = mysql_connect('192.168.2.1', 'abrelibros', 'bookworm');
	mysql_select_db('abrelibros',$db);
	return ($db);
}

function dbEscape($field, $db) {
   if (get_magic_quotes_gpc()) $field=stripslashes($field);
   $field = mysql_real_escape_string($field, $db);
   return $field;
}