<?php
function formatmonth($m) {
	$d=mktime(12,0,0,substr($m, 4, 2),1,substr($m, 0, 4));
	$strdate=gmdate("M Y",$d);
	return $strdate;
}
	
session_start();
$loggedin=false;
$fpath="";
$hash="";

if (isset($_POST['u']) && isset($_POST['p'])) {
	$hash=md5($_POST['u']."uphashseed".$_POST['p']);
} elseif (isset($_SESSION['userhash'])) {
	$hash=$_SESSION['userhash'];
}

$fpath=realpath('logs/'.$hash.'.log');
if (file_exists($fpath)) $loggedin=true;
	
if (!$loggedin) {
	if ($hash!="") {
		unset($_SESSION['userhash']);
		header("Location: download.php?h=".$hash);
		exit;
	} else {
		unset($_SESSION['userhash']);
		header("Location: index.php?e=1");
		exit;
	}
}

if (isset($_POST['u'])) {
	$_SESSION['username'] = $_POST['u'];
}

$username = $_SESSION['username'];
$_SESSION['userhash']=$hash;

$days=array();
$months=array();
$pausestart=0;
$f=fopen($fpath, 'r');
while ($line=fgets($f)) {
	if (trim($line)=="") continue;
	if (substr($line,0,1)=="#") continue;
	$coming=(substr($line,0,1)=="+");
	
	$datetime=strtotime(substr($line,2,19));
	$monthy=date("Ym",$datetime);
	
	if (!isset($months[$monthy])) {
		$months[$monthy]=0;
	}
	$date=substr($line,2,10);
	
	if (!isset($days[$date])) {
		$pausestart=0;
		$days[$date]=array(
			'month'=>$monthy,
			'date'=>$date,
			'datestamp'=>strtotime($date),
			'start'=>substr($line,13, 8),
			'startstamp'=>$datetime,
			'pause'=>0,
		);
	} elseif (substr($line,0,1)=="C") {
		echo "C".$days[$date]['start'];
		$days[$date]['startstamp']-=substr($line,22);
		continue;
	} elseif (!$coming) {
		$pausestart=$datetime;
	} else {
		$days[$date]['pause']+=($datetime-$pausestart);
	}
	
	if ($coming && $date==date("Y-m-d")) $datetime=time();
	
	$days[$date]['end']=substr($line,13, 8);
	$days[$date]['endstamp']=$datetime;
	
	$worktime=$datetime-$days[$date]['startstamp'];
	$days[$date]['worktime']=$worktime;
	$solldiff=60*525;
	$days[$date]['diff']=$worktime-$solldiff;
	
	$olddate=$date;
}
fclose($f);

//$days[$date]['endstamp']=$datetime;

//unset($days[$date]);

$curmonth=date("Ym");
if (isset($_GET['m'])) {
	$curmonth=$_GET['m'];
}

$vals=array();
$valsdif=array();
$daynames=array();
$alt=true;

foreach ($days as &$day) {
	$months[$day['month']]+=$day['diff']-$day['pause'];
	$day['monthdiff']=$months[$day['month']];
	$day['diff']=$day['diff']-$day['pause'];
	
	if ($curmonth==$day['month']) {
		$bc=gmdate("i",$day['worktime']-$day['pause']);
		$part=$bc/60;
		$floattime=gmdate("G",$day['worktime']-$day['pause'])+$part;
		$vals[]=$floattime;
		$daynames[]=date("d.",$day['datestamp']);
		
		$bc=gmdate("i",$day['monthdiff']*-1);
		$part=$bc/60;
		
		if ($day['monthdiff']>0) {
			$bc=gmdate("i",$day['monthdiff']);
			$part=$bc/60;
			$floattime=gmdate("G",$day['monthdiff'])+$part;
		} else {
			$bc=gmdate("i",$day['monthdiff']*-1);
			$part=$bc/60;
			$floattime=(gmdate("G",$day['monthdiff']*-1)+$part)*-1;
		}
		$valsdif[]=$floattime;
	}
}

include "views/show.phtml";

