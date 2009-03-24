<?php
if ($_GET['d']=="y") {
	$pathparts=pathinfo($_SERVER['SCRIPT_NAME']);
	$scriptpath=$pathparts['dirname'];
	$daemon=file_get_contents('desktop/screenidle.pl');
	$daemon=str_replace('%%URL%%', 'http://'.$_SERVER['SERVER_NAME'].$scriptpath.'/log.php', $daemon);
	$daemon=str_replace('%%HASH%%', $_GET['h'], $daemon);
	
	header("Content-Length: ".strlen($daemon));
	header("Content-Disposition: attachment; filename=screenidle.pl");
	header("Content-Type: application/x-perl");
	echo $daemon;
	exit;
}
?>
<!DOCTYPE html>

<html lang="en">
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<title>Daemon downloaden</title>
	<meta name="generator" content="TextMate http://macromates.com/">
	<meta name="author" content="">
</head>
<body>
<h1>Daemon herunterladen</h1>
<p>
	Der daemon ist fertig konfiguriert und muss nur so installiert werden, dass er beim Starten
	des Systems immer läuft!
</p>
	<a href="<?php echo $_SERVER['REQUEST_URI'] ?>&d=y">Download</a>
</body>
</html>

