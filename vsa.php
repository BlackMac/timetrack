<?php
require_once "TimeTrack.class.php";
$tt = new TimeTrack();

function getTimes($tt) {
	$tt->parseData();
	$ld=$tt->getLastDay();
	//print_r($ld);
	echo((isset($ld['diff']) ? $ld['diff'] : 0)."\n");
	echo((isset($ld['monthdiff']) ? $ld['monthdiff'] : 0)."\n");
	echo((isset($ld['laststateIn']) ? (int)$ld['laststateIn'] : 0)."\n");
	echo((isset($ld['start']) ? $ld['start'] : 0) ."\n");
	echo((isset($ld['pause']) ? $ld['pause'] : 0) ."\n");
}

error_reporting(E_ALL);
ini_set("display_errors", 1);

$action=isset($_GET['a']) ? $_GET['a'] : '';
$hash=isset($_GET['h']) ? $_GET['h'] : '';

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