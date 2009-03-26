<?php
session_start();

if (!isset($_POST) || count($_POST) <= 0) {
	echo "Wrong method";
	exit;
}

if (!isset($_POST['action']) || empty($_POST['action'])) {
	echo "No action specified";
	exit;
}

if (isset($_SESSION['userhash'])) {
	$hash = $_SESSION['userhash'];
} else {
	echo "No Hash in session given";
	exit;
}

switch($_POST['action']) {
	case 'changeuserdata':
		changeUserData($hash);
		break;
	default:
		echo "Wrong action specified";
		break;
}
exit;

function changeUserData($hash) {
	if (!isset($_POST['changeuserdata_user'], $_POST['changeuserdata_pass'])) {
		echo 'changeUserData: Required fields are missing';
		return;
	}

	$fpath=realpath('logs/'.$hash.'.log');
	
	$newhash = md5($_POST['changeuserdata_user']."uphashseed".$_POST['changeuserdata_pass']);
	$fnewpath= dirname($fpath) . '/'.$newhash.'.log';

	$result = rename($fpath, $fnewpath);
	if($result) {
		$_SESSION['userhash'] = $newhash;
		header("Location: show.php");
	} else {
		echo "changeUserData: Error occurred during renaming.";
		return;
	}
}
