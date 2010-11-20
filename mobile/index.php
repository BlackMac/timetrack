<?php
	session_start();
	include "../TimeTrack.class.php";
	$timetrack = new TimeTrack();

	$loggedin = $timetrack->login($_POST['u'], $_POST['p'], $_GET['h']);
	$hash = $timetrack->hash;

	if(!$loggedin) {
		unset($hash);
		unset($_SESSION['userhash']);
	}

include 'index.phtml';