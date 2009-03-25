<?php
if ($_GET['d']=="y") {
	$pathparts=pathinfo($_SERVER['SCRIPT_NAME']);
	$scriptpath=$pathparts['dirname'];
	$daemon=file_get_contents('desktop/screenidle.pl');
	$daemon=str_replace('%%URL%%', 'http://'.$_SERVER['SERVER_NAME'].$scriptpath.'/log.php', $daemon);
	$daemon=str_replace('%%HASH%%', $_GET['h'], $daemon);
	
	header("Content-Length: ".strlen($daemon));
	header("Content-Disposition: attachment; filename=screenidle.pl");
	header("Content-Type: application/x-perl");
	echo $daemon;
	exit;
}

include "views/download.phtml";

