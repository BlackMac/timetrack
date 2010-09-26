<?php
include "functions_GRML.php";
include "TimeTrack.class.php";

session_start();
$timetrack = new TimeTrack();
$mobiledevice = detectMobileDevices();

$loggedin=false;
$loggedin = $timetrack->login($_POST['u'], $_POST['p']);

if (!$loggedin) {
	if ($timetrack->generateHash($_POST['u'], $_POST['p']) ) {
		unset($_SESSION['userhash']);
		header("Location: download.php?h=". $timetrack->generateHash($_POST['u'], $_POST['p']) );
		exit;
	} else {
		unset($_SESSION['userhash']);
		header("Location: index.php?e=1");
		exit;
	}
}

$curmonth=date("Ym");
if (isset($_GET['m'])) {
	$curmonth=$_GET['m'];
}

$timetrack->setMonth($curmonth);
$data = $timetrack->parseData();

$alt=true;

$day = $timetrack->getLastDay();

$filename = pathinfo(__FILE__, PATHINFO_FILENAME);
include "views/$filename.phtml";

