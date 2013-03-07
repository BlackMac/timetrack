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

$forward = false;

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
		$forward = true;
	}
}

if(isset($_POST['daysize']) && isset($_POST['date']))
{
	$timetrack->changeDailyWorkingTimeForADay($_POST['date'], $_POST['daysize']);
	$oldstart = strtotime($_POST['date']);
	$forward = true;
}

if($forward === true)
{
	$month = date("Ym", $oldstart);
	header('Location: show.php?m=' . $month);
}

$timetrack->setMonth(date("Ym",strtotime($curdate)));
$data = $timetrack->parseData();
$day = $data['days'][$curdate];

if(!isset($day)) {
	die('Day not found!');
}

include "views/correct.phtml";
