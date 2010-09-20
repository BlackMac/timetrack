<?php
if (!isset($_GET['h']) || strlen($_GET['h']) != 32) {
	echo "No Hash given";
	exit;
}

$logtime=date("Y-m-d\TH:i:s");
$action="#";

if ($_GET['d']=="in") $action="+";
if ($_GET['d']=="out") $action="-";

$logline=$action.'['.$logtime.'] ***'.$_GET['d'].'***'."\r\n";

if(is_file('logs/'.$_GET['h'].'.log')) {
	$filename = 'logs/'.$_GET['h'].'.log';
} elseif(is_dir('logs/'.$_GET['h'])) {
	$filename = 'logs/'.$_GET['h'].'/month_'. date('Ym') .'.log';
} else {
	mkdir('logs/'.$_GET['h']);
	$filename = 'logs/'.$_GET['h'].'/month_'. date('Ym') .'.log';
}

$file=fopen($filename, 'a');
fputs($file, $logline);
fclose($file);

echo $logline;
