<?php
require_once ("TimeTrackSQL.class.php");

$tt= new TimeTrack();

if (isset($_GET['register'])) {
	$tt->register('stefan', 'stefan');
	die ('added');
}



if (!$tt->login($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
	header('WWW-Authenticate: Basic realm="time.track"');
    header('HTTP/1.0 401 Unauthorized');
    echo 'Not authorized to access page!';
    exit;
}

if (isset($_GET['last'])) {
	print_r($tt->lastEvent());
	die ('');
}

$tt->addEvent(TT_DIRECTION_OUT);
?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>untitled</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="Stefan Lange-Hegermann">
	<!-- Date: 2009-09-08 -->
</head>
<body>
<?php

?>
</body>
</html>
