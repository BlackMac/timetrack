<?php
include "functions_GRML.php";
include "TimeTrack.class.php";

session_start();
$timetrack = new TimeTrack();
TimeTrack::$migrationMode = true;
$mobiledevice = detectMobileDevices();

$loggedin=false;
$loggedin = $timetrack->login($_POST['u'], $_POST['p'], $_GET['h']);
$hash = $timetrack->hash;

if(!$loggedin) {
		unset($_SESSION['userhash']);
		header("Location: index.php?e=1");
		exit;
}

if(isset($_POST['migrate'])) {
  $res = $timetrack->migrateFileToDir($hash);
  if($res['error'] === false) {

    header("Location: show.php");
    die();

  }
}

$filename = pathinfo(__FILE__, PATHINFO_FILENAME);
include "views/$filename.phtml";
