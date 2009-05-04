<?php
include "functions_GRML.php";
include "TimeTrack.class.php";

session_start();
$timetrack = new TimeTrack();
$mobiledevice = detectMobileDevices();
$curdate = $_REQUEST['date'];

if(!isset($curdate)) {
	die('Wrong parameter!');
}

$loggedin=false;
$loggedin = $timetrack->login($_POST['u'], $_POST['p'], $_GET['h']);
$hash = $timetrack->hash;

if(!$loggedin) {
	unset($hash);
	unset($_SESSION['userhash']);
}

if (!$loggedin) {
	if ($hash!="") {
		unset($_SESSION['userhash']);
		header("Location: download.php?h=".$hash);
		exit;
	} else {
		unset($_SESSION['userhash']);
		header("Location: index.php?e=1");
		exit;
	}
}

$formsend = $_POST['submit'];
$newstart = $_POST['newstart'];
$newend = $_POST['newend'];
$oldstart = $_POST['oldstart'];
$oldend = $_POST['oldend'];

if(isset($formsend, $newstart, $newend, $oldstart, $oldend)) {

	$success = true;

	if($oldstart != $newstart) {
		$success = $timetrack->updateFile($oldstart, $newstart);
	}

	if($oldend != $newend) {
		$success = $timetrack->updateFile($oldend, $newend);
	}

	if(!$success) {
		die("Update not successful");
	} else {
		header('Location: show.php');
	}
}

$data = $timetrack->parseData();
$day = $data['days'][$curdate];

if(!isset($day)) {
	die('Day not found!');
}

include "views/correct.phtml";
