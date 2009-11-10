<?php
function redirect($url='/') {
	header("Location: /sql.php?a=".$url);
	exit;
}

require_once ("TTActions.class.php");
$ac= new TTActions();
if (!isset($_GET['a'])) {
	$action='loginAction';
} else {
	$action=$_GET['a'].'Action';
}

if (!method_exists($ac, $action)) {
	header("HTTP/1.1 404 Not Found");
	echo $action.' 404';
	exit;
}
$content=$ac->$action();

if (!$content) {
	header("HTTP/1.1 405 Not Found");
	echo $action.' 405';
	exit;
}
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
	require_once('SViews/'.$content['view'].'.phtml')
?>
</body>
</html>
<?php
die();

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

$tt->importText('32e432f8caef726ff8232ea76a7181f2');

$ev=$tt->getEvents();

foreach ($ev as $event) {
	print_r($event);
	echo '<br><br>';
}
/*if (isset($_GET['last'])) {
	print_r($tt->lastEvent());
	die ('');
}*/

//$tt->addEvent(TT_DIRECTION_OUT);
?>

