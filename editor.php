<?php
include "functions_GRML.php";
include "TimeTrack.class.php";
// Comment
session_start();
$timetrack = new TimeTrack();

$loggedin=false;
$loggedin = $timetrack->login($_POST['u'], $_POST['p'], $_GET['h']);
$hash = $timetrack->hash;

if(!$loggedin) {
	unset($hash);
	unset($_SESSION['userhash']);
}

if (!$loggedin) {
	die('Not logged in');
}

$logfileRaw = $_POST['logfileRaw'];
$postHash = $_POST['hash'];
$postIp = $_POST['ip'];
$postTimestamp = $_POST['timestamp'];

if(isset($logfileRaw, $postHash, $postIp, $postTimestamp)) {

	$matches = array();
	if(preg_match("/^[-\+#]\[(\d{4}-\d{2}-\d{2}\w\d{2}:\d{2}:\d{2})\]\s.*/", $logfileRaw, $matches))
	{
		$curmonth = date("Ym", strtotime($matches[1]));
	}

	if($postHash != $timetrack->hash || $postIp != $_SERVER['REMOTE_ADDR']) {
		$msg = "Something went wrong. Security Information did not match.";
	} else
	if(time() - $postTimestamp < 5) {
		$msg = "Flash Gordon was here. Try to breathe in and out and give yourself more time to edit.";
	} else
	if(!$timetrack->writeFile($logfileRaw, $curmonth)) {
		$msg = "Update not successful";
	} else
	{
		$msg = '';
		header('Location: show.php?m=' . $curmonth);
	}

	die($msg);
}

if(isset($_GET['m'])) {
	$timetrack->setMonth($_GET['m']);
}

include "views/editor.phtml";
