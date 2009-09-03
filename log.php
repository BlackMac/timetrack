<?php
if (!isset($_GET['h'])) {
	echo "No Hash given";
	exit;
}

$logtime=date("Y-m-d\TH:i:s");
$action="#";

if ($_GET['d']=="in") $action="+";
if ($_GET['d']=="out") $action="-";

$logline=$action.'['.$logtime.'] ***'.$_GET['d'].'***'."\r\n";

$file=fopen('logs/'.$_GET['h'].'.log', 'a');
fputs($file, $logline);
fclose($file);

echo $logline;
