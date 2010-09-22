<?php
require_once "TimeTrack.class.php";
$tt = new TimeTrack();

function getTimes($tt) {
	$tt->parseData();
	$ld=$tt->getLastDay();
	//print_r($ld);
	echo($ld['diff']."\n");
	echo($ld['monthdiff']."\n");
	echo((int)$ld['laststateIn']."\n");
	echo($ld['start']."\n");
	echo($ld['pause']."\n");
}

error_reporting(E_ALL);
ini_set("display_errors", 1);

$action=$_GET['a'];
$hash=$_GET['h'];

if (!$tt->login(null, null, $hash)) {
	die('INVALID LOGIN');
}

$curmonth=date("Ym");
if (isset($_GET['m'])) {
	$curmonth=$_GET['m'];
}

$tt->setMonth($curmonth);

if ($action=="times") {
	getTimes($tt);
}

if ($action=="login") {
	getTimes($tt);
}

if ($action=="logout") {
	getTimes($tt);
}
?>