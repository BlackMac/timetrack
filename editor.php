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

	if($postHash != $timetrack->hash || $postIp != $_SERVER['REMOTE_ADDR']) {
		$msg = "Something went wrong. Security Information did not match.";
	} else 	
	if(time() - $postTimestamp < 5) {
		$msg = "Flash Gordon was here. Try to breathe in and out and give yourself more time to edit.";
	} else
	if(!$timetrack->writeFile($logfileRaw)) {
		$msg = "Update not successful";
	} else
	{
		$msg = '';
		header('Location: show.php');
	}
	
	die($msg);
}

include "views/editor.phtml";
